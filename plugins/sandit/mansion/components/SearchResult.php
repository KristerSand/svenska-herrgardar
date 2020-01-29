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
        
        // Paginering
        $perPage = $this->property('postsPerPage');
        $currentPage = Input::get('page') ?: 1;
        $slice_init = ($currentPage == 1) ? 0 : (($currentPage*$perPage)-$perPage);
        $pagedData = $result->slice($slice_init, $perPage)->all();
        $result = new LengthAwarePaginator($pagedData, count($result), $perPage, $currentPage);
        $result->setPath('sokresultat');
        $this->result = $result;
    }

    public function onShowMansionData()
    {
        $gard_id = Input::get('gard_id');
        $search_repo = App::make('SearchRepositoryInterface');
        $gard_data = $search_repo->getGardar([$gard_id], 'id', ['post'])->first();
        
        return [
            '#gard-info-'.$gard_id => $this->renderPartial('@gard-info', ['gard_data' => $gard_data])
        ];
    }
}