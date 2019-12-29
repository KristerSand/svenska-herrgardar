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
    protected $description = 'Does something cool.';



    private function create_marker($name,$tora_id,$lon, $lat)
    {
        $marker = new Marker();
        $marker->name = "klk";
        $marker->lat = 59.00;
        $marker->lon = 18.00;
        $marker->tora_id = 222;
        $marker->save();
    }


    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {
        $this->output->writeln('Hello world!');
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