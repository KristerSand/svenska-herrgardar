<?php namespace Sandit\Mansion\Classes\Suecia;

class Suecia {
    public static function hasSueciaImages($toraid) {
        $sueciaurl = "https://tora.entryscape.net/store/search?type=solr&query=context:https%5C%3A%2F%2Ftora.entryscape.net%2Fstore%2F11+AND+rdfType:http%5C%3A%2F%2Fschema.org%2FImageObject+AND+metadata.predicate.uri.ac99d93f:https%5C%3A%2F%2Fdata.riksarkivet.se%2Ftora%2F" . $toraid . "+AND+metadata.predicate.literal.b5d28d0a:image%2Fjpeg&request.preventCache=1523136574811";
        $arrContextOptions=array(
            "ssl"=>array(
                    "verify_peer"=>true,
                    "verify_peer_name"=>true,
                     "cafile" =>"./cert.pem"
                ),
            );
        $suecia_json = file_get_contents($sueciaurl, false, stream_context_create($arrContextOptions));
        $suecia_record = json_decode($suecia_json); 
        $nr_of_image_records = count($suecia_record->resource->children);
        if ($nr_of_image_records > 0 ) {
            return true;
        } else {
            return false;
        }
       
    }
}
