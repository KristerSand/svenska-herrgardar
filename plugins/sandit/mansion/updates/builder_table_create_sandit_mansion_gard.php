<?php namespace Sandit\Mansion\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSanditMansionGard extends Migration
{
    public function up()
    {
        Schema::create('sandit_mansion_gard', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('id');
            $table->string('toraid', 10)->nullable();
            $table->string('namn');
            $table->integer('socken_id');
            $table->integer('tillhor_herrgard')->nullable();
            $table->string('nummer')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sandit_mansion_gard');
    }
}
