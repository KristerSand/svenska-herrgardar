<?php

$url_net = 'https://tora.entryscape.net/store/search?type=solr&query=rdfType:https%5C%3A%2F%2Fdata.riksarkivet.se%2Ftora%2Fschema%2FHistoricalSettlementUnit+AND+public:true+AND+(metadata.predicate.uri.abcbc6c6:https%5C%3A%2F%2Fdata.riksarkivet.se%2Ftora%2Ftorapartner%2Fherr)+AND+(title:***+OR+description:*+OR+tag.literal:***)&facetFields=metadata.predicate.uri.abcbc6c6,metadata.predicate.uri.023ae722,metadata.predicate.uri.957f77a7&request.preventCache=7'; // path to your JSON file
$file_url = "tora_all_manors.json";
$data = file_get_contents($file_url); // put the contents of the file into a variable
$characters = json_decode($data); // decode the JSON feed

echo "tora_id,lat,long\n";

foreach ($characters->resource->children as $character) {
    $tora_id = $character->entryId;
    $tora_uri = "https://data.riksarkivet.se/tora/" . $tora_id;
    $lat_uri = "http://www.w3.org/2003/01/geo/wgs84_pos#lat";
    $lat = str_replace(",", ".", $character->metadata->$tora_uri->$lat_uri[0]->value);
    $long_uri = "http://www.w3.org/2003/01/geo/wgs84_pos#long";
    $long =  str_replace(",", ".",$character->metadata->$tora_uri->$long_uri[0]->value);
	echo $tora_id . "," . $lat . "," . $long . "\n";
}