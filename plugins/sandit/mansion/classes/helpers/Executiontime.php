<?php namespace Sandit\Mansion\Classes\Helpers;


class ExecutionTime
{
    private $startTime;
    private $endTime;

    public function start()
    {
        $this->startTime = microtime(true);
    }

    public function end()
    {
        $this->endTime = microtime(true);
    }

    public function calculateTime()
    {
        $diff = ($this->endTime - $this->startTime);
        return number_format($diff, 2, '.', '');
    }

    public function __toString()
    {
        return "Executiontime: " . $this->calculateTime() . " s\n";
    }

    /**
     * USAGE
     * 
     * $executionTime = new ExecutionTime();
     * $executionTime->start();
     * // code
     * $executionTime->end();
     * echo $executionTime;
     */
}