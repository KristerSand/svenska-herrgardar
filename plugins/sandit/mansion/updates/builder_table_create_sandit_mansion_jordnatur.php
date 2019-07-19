<?php namespace Sandit\Mansion\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSanditMansionJordnatur extends Migration
{
    public function up()
    {
        Schema::create('sandit_mansion_jordnatur', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('namn', 150);
        });
    }

    public function down()
    {
        Schema::dropIfExists('sandit_mansion_jordnatur');
    }
}
