<?php namespace Sandit\Mansion\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSanditMansionJordnaturPost extends Migration
{
    public function up()
    {
        Schema::create('sandit_mansion_jordnatur_post', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('jordnatur_id')->unsigned();
            $table->integer('post_id')->unsigned();
            $table->primary(['jordnatur_id', 'post_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('sandit_mansion_jordnatur_post');
    }
}
