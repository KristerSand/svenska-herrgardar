<?php namespace Initbiz\LeafletPro\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use DB;
use Initbiz\leafletpro\models\Marker;

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


    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {
        $this->output->writeln('Hello world!');
        $csvFile = file('./plugins/initbiz/leafletpro/toraid.csv');
        $data = [];
        # Skip first line
        $row = 1;
        foreach ($csvFile as $line) {
            if($row==1)
            {
                # Skip row
            } else 
            {
                $data[] = str_getcsv($line);
            }
            $row++;
        }
        echo json_encode($data);
        $this->create_marker("foo",223,18.00, 58.00);
        
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