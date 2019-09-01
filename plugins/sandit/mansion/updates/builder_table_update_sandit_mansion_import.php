<?php namespace Sandit\Mansion\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSanditMansionImport extends Migration
{
    public function up()
    {
        Schema::table('sandit_mansion_import', function($table)
        {
            $table->string('file_name')->after('import_type')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('sandit_mansion_import', function($table)
        {
            $table->dropColumn('file_name');
        });
    }
}
