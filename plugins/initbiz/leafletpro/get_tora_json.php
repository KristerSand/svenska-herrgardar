<?php
function write_row($tora_record) {
    $tora_id = $tora_record->entryId;
    $tora_uri = "https://data.riksarkivet.se/tora/" . $tora_id;
    $lat_uri = "http://www.w3.org/2003/01/geo/wgs84_pos#lat";
    $long_uri = "http://www.w3.org/2003/01/geo/wgs84_pos#long";
    if (isset($tora_record->metadata->$tora_uri->$lat_uri) && isset($tora_record->metadata->$tora_uri->$long_uri)) {
        $lat = str_replace(",", ".", $tora_record->metadata->$tora_uri->$lat_uri[0]->value);
        $long =  str_replace(",", ".",$tora_record->metadata->$tora_uri->$long_uri[0]->value);
        echo $tora_id . "," . $lat . "," . $long . "\n";
    }
    
}

function write_rows($characters)
{
    foreach ($characters->resource->children as $character) {
        write_row($character);
    }
};

function construct_tora_url($offset)
{
    return "https://tora.entryscape.net/store/search?type=solr&query=rdfType:https%5C%3A%2F%2Fdata.riksarkivet.se%2Ftora%2Fschema%2FHistoricalSettlementUnit+AND+public:true+AND+(metadata.predicate.uri.abcbc6c6:https%5C%3A%2F%2Fdata.riksarkivet.se%2Ftora%2Ftorapartner%2Fherr)+AND+(title:***+OR+description:*+OR+tag.literal:***)&offset="
    . $offset . "&limit=500" . "&facetFields=metadata.predicate.uri.abcbc6c6,metadata.predicate.uri.023ae722,metadata.predicate.uri.957f77a7&request.preventCache=7"; 
};



function fetch_all_tora($nr_of_tora_records)
{
    #echo $nr_of_tora_records;

    $limit = 100;
    for ($offset=0 ; $offset < $nr_of_tora_records ; $offset = $offset + $limit ) { 
        
        $url_net = construct_tora_url($offset);
        #echo "Fetching url:" . $url_net;
        $data = file_get_contents($url_net); // put the contents of the file into a variable
        $tora_records = json_decode($data); // decode the JSON feed
        write_rows($tora_records);
    }

}

$url_net = 'https://tora.entryscape.net/store/search?type=solr&query=rdfType:https%5C%3A%2F%2Fdata.riksarkivet.se%2Ftora%2Fschema%2FHistoricalSettlementUnit+AND+public:true+AND+(metadata.predicate.uri.abcbc6c6:https%5C%3A%2F%2Fdata.riksarkivet.se%2Ftora%2Ftorapartner%2Fherr)+AND+(title:***+OR+description:*+OR+tag.literal:***)&facetFields=metadata.predicate.uri.abcbc6c6,metadata.predicate.uri.023ae722,metadata.predicate.uri.957f77a7&request.preventCache=7'; // path to your JSON file
$file_url = "tora_all_manors.json";
$data = file_get_contents($url_net); // put the contents of the file into a variable
$characters = json_decode($data); // decode the JSON feed
#echo json_encode($characters);
$nr_of_tora_records = $characters->results;
#echo $nr_of_tora_records;
echo "tora_id,lat,long\n";
fetch_all_tora($nr_of_tora_records);


