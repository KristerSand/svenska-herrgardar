<?php namespace Sandit\Mansion\Components;

use Cms\Classes\ComponentBase;
use Session;
use Input;
use App;
use Sandit\Mansion\Classes\Repositories\SearchRepositoryInterface;
use October\Rain\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;


class SearchResult extends ComponentBase
{
    public $result;
    public $searchform;
    public $input;
    public $gard_ids;
    
    public function componentDetails()
    {
        return [
            'name' => 'Lista av sökresultat',
            'description' => 'Lista av sökresultat'
        ];
    }

    public function defineProperties()
{
    return [
        'postsPerPage' => [
             'title'             => 'Poster per sida',
             'description'       => 'Antal poster per sida i sökresultatet',
             'default'           => 20,
             'type'              => 'string',
             'validationPattern' => '^[0-9]+$',
             'validationMessage' => 'Poster per sida kan endast bestå av siffror'
        ]
    ];
}

    public function onRun()
    {
        $this->addJs("assets/node_modules/proj4/dist/proj4.js");
        $this->addJs("assets/node_modules/handlebars/dist/handlebars.js");
        $this->addJs("assets/tora.js");
        $input = Session::get('input');

        $this->searchform = $input['searchform'];
        $search_repo = App::make('SearchRepositoryInterface');
        $result = $search_repo->search($input);
        $result = new Collection($result);
        //Put results in session so that they can be shown on map later
        Session::put("gard_resultset",$result);
        // Paginering
        $perPage = $this->property('postsPerPage');
        $currentPage = Input::get('page') ?: 1;
        $slice_init = ($currentPage == 1) ? 0 : (($currentPage*$perPage)-$perPage);
        $pagedData = $result->slice($slice_init, $perPage)->all();
        $result = new LengthAwarePaginator($pagedData, count($result), $perPage, $currentPage);
        $result->setPath('sokresultat');
        $this->result = $result;
    }


    private function hasSueciaImages($toraid) {
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

    public function onShowMansionData()
    {
        $gard_id = Input::get('gard_id');
        $search_repo = App::make('SearchRepositoryInterface');
        $gard_data = $search_repo->getGardar([$gard_id], 'id', ['post'])->first();
        $suecia = $this->hasSueciaImages($gard_data->toraid);
        
        return [
            '#gard-info-'.$gard_id => $this->renderPartial('@gard-info', ['gard_data' => $gard_data, 'suecia' => $suecia])
        ];
    }
}