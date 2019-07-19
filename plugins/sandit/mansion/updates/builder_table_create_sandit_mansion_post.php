<?php namespace Sandit\Mansion\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSanditMansionPost extends Migration
{
    public function up()
    {
        Schema::create('sandit_mansion_post', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('gard_id');
            $table->integer('ar_borjan')->nullable();
            $table->string('ar_borjan_anm')->nullable();
            $table->integer('ar_slut')->nullable();
            $table->string('ar_slut_anm')->nullable();
            $table->integer('status_id')->nullable();
            $table->string('ag_arr')->nullable();
            $table->string('typ')->nullable();
            $table->integer('agare_person_id')->nullable();
            $table->integer('maka1_person_id')->nullable();
            $table->integer('maka2_person_id')->nullable();
            $table->decimal('storlek_herrgard_mtl', 10, 2)->nullable();
            $table->decimal('storlek_har', 10, 2)->nullable();
            $table->decimal('storlek_aker_har', 10, 2)->nullable();
            $table->decimal('gods_mantal', 10, 2)->nullable();
            $table->decimal('gods_hektar', 10, 2)->nullable();
            $table->decimal('gods_aker_hektar', 10, 2)->nullable();
            $table->string('taxering')->nullable();
            $table->string('brukareforhallande')->nullable();
            $table->integer('kalla_id')->nullable();
            $table->string('kommentar')->nullable();
            $table->integer('import_id')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sandit_mansion_post');
    }
}