<?php namespace Sandit\Mansion\Tests\Models;

use Sandit\Mansion\Models\Status;
use PluginTestCase;

class StatusTest extends PluginTestCase
{
    public function testCreateFirstStatus()
    {
        $status = Status::create(['namn' => 'Hi!']);
        $this->assertEquals(1, $status->id);
    }

    public function testFindMostFrequentWithEmptyValues()
    {
        $statuses = null;
        $expected = '';

        $status = Status::findMostFrequentStatus($statuses);

        $this->assertEquals($expected, $status);
    }

    public function testFindMostFrequentWithSingleValues()
    {
        // Arrange
        $statuses = 'Bondgård';
        $expected = 'Bondgård';

        // Act
        $status = Status::findMostFrequentStatus($statuses);

        // Assert
        $this->assertEquals($expected, $status);
    }

    public function testFindMostFrequentWithMultipleValues()
    {
        // Arrange
        $statuses = 'Herrgård,Ståndsgård,Ståndsgård,Herrgård,Ståndsgård,Bondgård';
        $expected = 'Ståndsgård';

        // Act
        $status = Status::findMostFrequentStatus($statuses);

        // Assert
        $this->assertEquals($expected, $status);
    }

    public function testFindMostFrequentWithHerrgardAndMultipleBondgardar()
    {
        // Arrange
        $statuses = 'Bondgård,Bondgård,Herrgård,Bondgård';
        $expected = 'Herrgård';

        // Act
        $status = Status::findMostFrequentStatus($statuses);

        // Assert
        $this->assertEquals($expected, $status);
    }

    public function testFindMostFrequentWithStandsgardAndMultipleBondgardar()
    {
        // Arrange
        $statuses = 'Bondgård,Bondgård,Ståndsgård,Bondgård';
        $expected = 'Ståndsgård';

        // Act
        $status = Status::findMostFrequentStatus($statuses);

        // Assert
        $this->assertEquals($expected, $status);
    }

    public function testFindMostFrequentReturnsHerrgardWithHerrgardAndStandsgard()
    {
        // Arrange
        $statuses = 'Herrgård,Ståndsgård';
        $expected = 'Herrgård';

        // Act
        $status = Status::findMostFrequentStatus($statuses);

        // Assert
        $this->assertEquals($expected, $status);
    }
}