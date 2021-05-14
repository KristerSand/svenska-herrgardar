<?php namespace Sandit\Mansion\Tests\Helpers;

use Sandit\Mansion\Classes\Helpers\Executiontime;
use PluginTestCase;

class ExecutiontimeTest extends PluginTestCase
{
    public function test()
    {
        // Arrange
        
        $expected = 2;

        // Act
        $executiontime = new Executiontime;
        $executiontime->start();
        sleep(2);
        $executiontime->end();
        $received = $executiontime->calculateTime();

        // Assert
        $this->assertGreaterThanOrEqual($expected, $received);
    }
}