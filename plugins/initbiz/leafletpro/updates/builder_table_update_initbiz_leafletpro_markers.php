<?php namespace Initbiz\LeafletPro\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateInitbizLeafletproMarkers extends Migration
{
    public function up()
    {
        Schema::table('initbiz_leafletpro_markers', function($table)
        {
            $table->integer('tora_id')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('initbiz_leafletpro_markers', function($table)
        {
            $table->dropColumn('tora_id');
        });
    }
}
