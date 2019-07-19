<?php namespace Sandit\Mansion\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSanditMansionPerson extends Migration
{
    public function up()
    {
        Schema::create('sandit_mansion_person', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('namn')->nullable();
            $table->string('efternamn')->nullable();
            $table->string('titel_tjanst')->nullable();
            $table->string('titel_familj')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('sandit_mansion_person');
    }
}
