<?php namespace Initbiz\LeafletPro\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
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

    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {
        $this->output->writeln('Hello world!');
        $marker = new Marker();
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