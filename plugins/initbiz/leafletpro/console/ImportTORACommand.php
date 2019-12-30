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
    protected $description = 'Imports TORA ID coordinates into Marker table from CSV-file';



    private function create_marker($name,$tora_id,$lon, $lat)
    {
        $marker = new Marker();
        $marker->name = $name;
        $marker->lat = $lat;
        $marker->lon = $lon;
        $marker->tora_id = $tora_id;
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

    private function fetch_tora_post($tora_id) 
    {
        $tora_url = $this->construct_tora_url($tora_id);
        echo "Fetched record " . $tora_url;
        $data = file_get_contents($tora_url); // put the contents of the file into a variable
        $tora_record = json_decode($data); 
        
    }


    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {
        $this->output->writeln('Importing TORA IDs');
        $csvFile = file('./plugins/initbiz/leafletpro/toraid_all.csv');
        # Loop through all manors and get coordinates from TORA by API
        Gard::each(function ($gard) 
        {
            if(isset($gard->toraid)) 
            {
                $tora_post = $this->fetch_tora_post($gard->toraid);
                $tora_uri = "https://data.riksarkivet.se/tora/" . $gard->toraid;
                $lat_uri = "http://www.w3.org/2003/01/geo/wgs84_pos#lat";
                $long_uri = "http://www.w3.org/2003/01/geo/wgs84_pos#long";
                if(isset($tora_record->metadata->$tora_uri->$lat_uri) && isset($tora_record->metadata->$tora_uri->$long_uri))
                {
                    $this->create_marker($gard->namn,$gard->toraid,$tora_record->metadata->$tora_uri->$long_uri, $tora_record->metadata->$tora_uri->$lat_uri);
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