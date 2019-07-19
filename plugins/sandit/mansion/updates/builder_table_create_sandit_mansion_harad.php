<?php namespace Sandit\Mansion\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSanditMansionHarad extends Migration
{
    public function up()
    {
        Schema::create('sandit_mansion_harad', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('namn');
            $table->integer('landskap_id');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('sandit_mansion_harad');
    }
}
