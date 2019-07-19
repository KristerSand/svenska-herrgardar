<?php namespace Sandit\Mansion\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSanditMansionKalla extends Migration
{
    public function up()
    {
        Schema::create('sandit_mansion_kalla', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('namn');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('sandit_mansion_kalla');
    }
}
