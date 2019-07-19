<?php namespace Sandit\Mansion\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSanditMansionImport extends Migration
{
    public function up()
    {
        Schema::create('sandit_mansion_import', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('import_type', 191)->default('mansion');
            $table->integer('total_rows')->nullable();
            $table->integer('saved_rows')->nullable();
            $table->integer('user_id')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sandit_mansion_import');
    }
}
