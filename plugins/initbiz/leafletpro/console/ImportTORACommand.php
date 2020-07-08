<?php namespace Initbiz\LeafletPro\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use DB;
use Initbiz\leafletpro\models\Marker;
use Sandit\Mansion\Models\Gard;


class ImportTORACommand extends Command
{
    /**
     * @var string The console command name.
     */
    protected $name = 'acme:importtoraids';

    /**
     * @var string The console command description.
     */
    protected $description = 'Imports TORA ID coordinates into Marker table for all manors that have a TORA-id';



    private function create_marker($name,$gard_id,$tora_id,$lon, $lat)
    {
        $marker = new Marker();
        $marker->name = $name;
        $marker->lat = $lat;
        $marker->lon = $lon;
        $marker->tora_id = $tora_id;
        $marker->gard_id = $gard_id;
        $marker->save();
    }


    private function import_from_csv() 
    {
        $csvFile = file('./plugins/initbiz/leafletpro/toraid_all.csv');
        $data = [];
        # Skip first line
        $row = 1;
        foreach ($csvFile as $line) {
            if($row==1)
            {
                # Skip row
            } else 
            {
                $row_data = str_getcsv($line);
                $tora_id = $row_data[0];
                $long = $row_data[2];
                $lat = $row_data[1];
                $this->create_marker("foo",$tora_id,$long, $lat);
            }
            $row++;
        }       
    }

    private function construct_tora_url($tora_id)
    {
        return "https://tora.entryscape.net/store/61/entry/" . $tora_id. "?includeAll&format=application/json";
    }

    private function get_tora_json_from_url($tora_url)
    {
        $arrContextOptions=array(
        "ssl"=>array(
                "verify_peer"=>true,
                "verify_peer_name"=>true,
                 "cafile" =>"./cert.pem"
            ),
        );
        return file_get_contents($tora_url, false, stream_context_create($arrContextOptions));
    }


    private function fetch_tora_post($tora_id) 
    {
        $tora_url = $this->construct_tora_url($tora_id);
        #echo "Fetched record " . $tora_url;
        $data = $this->get_tora_json_from_url($tora_url); // put the contents of the file into a variable
        $tora_record = json_decode($data); 
        return $tora_record;
    }

    private function delete_markers() 
    {
        Marker::truncate();
    }


    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {
        $this->output->writeln('Importing TORA IDs');
        # Delete all previous markers
        $this->delete_markers();
        # Loop through all manors and get coordinates from TORA by API
        Gard::each(function ($gard) 
        {
            if(isset($gard->toraid)) 
            {
                $tora_post = $this->fetch_tora_post($gard->toraid);
                $tora_uri = "https://data.riksarkivet.se/tora/" . $gard->toraid;
                $lat_uri = "http://www.w3.org/2003/01/geo/wgs84_pos#lat";
                $long_uri = "http://www.w3.org/2003/01/geo/wgs84_pos#long";
                echo "\n";
                if(isset($tora_post->metadata->$tora_uri->$lat_uri) && isset($tora_post->metadata->$tora_uri->$long_uri))
                {
                    echo "Creating marker for Gard: " . $gard->id . " with TORA-id: " . $gard->toraid;
                    $lat = str_replace(",", ".", $tora_post->metadata->$tora_uri->$lat_uri[0]->value);
                    $lon =  str_replace(",", ".",$tora_post->metadata->$tora_uri->$long_uri[0]->value);
                    $this->create_marker($gard->namn,$gard->id, $gard->toraid,$lon,$lat);
                } else {
                    echo "No coordinates exist for TORA-id: " . $gard->toraid;
                }
                
                
            }
        });
        
    }

    

    /**
     * Get the console command arguments.
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }

    /**
     * Get the console command options.
     * @return array
     */
    protected function getOptions()
    {
        return [];
    }

}