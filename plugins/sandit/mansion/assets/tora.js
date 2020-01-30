
proj4.defs("EPSG:3006","+proj=utm +zone=33 +ellps=GRS80 +towgs84=0,0,0,0,0,0,0 +units=m +no_defs");

function constructImageURLList(imageURLs) {
    var urlListHTML = ""
     imageURLs.forEach(function(imageURL) {
        urlListHTML = urlListHTML + "<li><a href='" + imageURL + "'>Se bild</a></li>"
     })
     return "<ol>" + urlListHTML + "</ol>"
}

function getImagesSuccess(imagesJSON) {

    var title= "Suecia bilder"
    var imageURLs = imagesJSON.resource.children.map(function (child) {return Object.keys(child.metadata)[0]});
    $("#feature-title").html(title);
    $("#feature-info").html(constructImageCarousel(imageURLs));
    $("#featureModal").modal("show");
}

function constructImageCarousel(imageURLs) {
    var carouselTemplateID = "sueciaCarousel";
    var carouselHtmlTemplate = $("#"+carouselTemplateID).html();
    var carouselHtmlTemplateCompiled = Handlebars.compile(carouselHtmlTemplate);
     var context = {"urls":imageURLs}
     return carouselHtmlTemplateCompiled(context)
}

function getSueciaImages(toraID, successFunction) {
    console.log("Getting suecia for "+toraID);
    var url = "https://tora.entryscape.net/store/search?type=solr&query=context:https%5C%3A%2F%2Ftora.entryscape.net%2Fstore%2F11+AND+rdfType:http%5C%3A%2F%2Fschema.org%2FImageObject+AND+metadata.predicate.uri.ac99d93f:https%5C%3A%2F%2Fdata.riksarkivet.se%2Ftora%2F"+toraID+"+AND+metadata.predicate.literal.b5d28d0a:image%2Fjpeg&request.preventCache=1523136574811&callback=?"
    $.getJSON(url, function (data) {
        successFunction(data)
        console.log(data)
        sueciaJSON = data
        return sueciaJSON;
    });

}

function get_tora_longitude_sweref99(tora_entry) {

    var entryURI = Object.keys(tora_entry.metadata)[0]
    if(tora_entry.metadata[entryURI].hasOwnProperty("https://data.riksarkivet.se/tora/schema/sweref99_n")){
        return parseFloat(tora_entry.metadata[entryURI]["https://data.riksarkivet.se/tora/schema/sweref99_n"][0].value.replace(",", "."))
    }
else
    {
        return null;
    }

}

function get_tora_latitude_sweref99(tora_entry) {
    var entryURI = Object.keys(tora_entry.metadata)[0]
    if(tora_entry.metadata[entryURI].hasOwnProperty("https://data.riksarkivet.se/tora/schema/sweref99_e")){
        return parseFloat(tora_entry.metadata[entryURI]["https://data.riksarkivet.se/tora/schema/sweref99_e"][0].value.replace(",", "."));
    } else {
        return null;
    }
}


function get_tora_json(toraid, success_function) {
    var tora_uri = "https://tora.entryscape.net/store/61/entry/" + toraid+ "?includeAll&limit=50&sort=title&prio=List&format=application/json"
    $.getJSON(tora_uri, success_function);
}

function tora_uri(tora_id) {
    return "https://data.riksarkivet.se/tora/"+tora_id;
}

function construct_historical_map_search_url(tora_id)
{
    function get_sweref_lat_long(data) {
        console.log(get_tora_latitude_sweref99(data));
        console.log(get_tora_longitude_sweref99(data));
    }
    get_tora_json(tora_id,get_sweref_lat_long);
    var url_template = "https://historiskakartor.lantmateriet.se/historiskakartor/searchresult.html?archive=GEOIN&firstMatchToReturnLMS=1&firstMatchToReturnREG=1&firstMatchToReturnRAK=1&yMin=6607185&xMin=701600&yMax=6608185&xMax=702600";
    var historical_maps_link_id ="historical_maps_link"
    $("#"+historical_maps_link_id).attr("href","#0");
    alert($("#"+historical_maps_link_id));
}