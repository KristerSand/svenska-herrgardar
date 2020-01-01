<?php namespace Initbiz\LeafletPro;

use DB;
use Initbiz\LeafletPro\models\Marker;

class TORAImporter
{
    public function import() 
    {
        echo "klk";
        $tora_marker = new Marker();
    }
    
}

$tora_importer = new TORAImporter();
$tora_importer->import();


