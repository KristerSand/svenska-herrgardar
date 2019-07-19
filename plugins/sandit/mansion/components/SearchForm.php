<?php namespace Sandit\Mansion\Components;

use Cms\Classes\ComponentBase;
use Sandit\Mansion\Models\Landskap;
use Sandit\Mansion\Models\Harad;
use Sandit\Mansion\Models\Socken;
use Sandit\Mansion\Models\Status;
use Sandit\Mansion\Models\Jordnatur;
use Sandit\Mansion\Models\Person;
use Input;
use Redirect;
use Session;
use DB;

class SearchForm extends ComponentBase
{
    public $landskap;
    public $harad;
    public $socken;
    public $status;
    public $jordnatur;
    public $agar_arr;
    public $searchform;
    public $person_titel_tjanst;
    public $person_titel_familj;
    public $maka_titel_tjanst;
    public $maka_titel_familj;


    public function componentDetails()
    {
        return [
            'name' => 'Sökformulär',
            'description' => 'Sökning för herrgårdsdatabasen'
        ];
    }

    public function onRun()
    {
        $this->landskap = $this->loadLandskap();
        $this->harad = $this->loadHarad();
        $this->socken = $this->loadSocken();
        $this->status = $this->loadStatus();
        $this->jordnatur = $this->loadJordnatur();
        $this->agar_arr = $this->loadAgarArr();
        $this->person_titel_tjanst = $this->loadTitle('person_titel_tjanst');
        $this->person_titel_familj = $this->loadTitle('person_titel_familj');
        $this->maka_titel_tjanst = $this->loadTitle('maka_titel_tjanst');
        $this->maka_titel_familj = $this->loadTitle('maka_titel_familj');

        if (Session::has('input')) {
            $input = Session::pull('input');
            $this->searchform = $input['searchform'];
        } else {
            $this->searchform = "form";
        }
    }

    public function onSearch()
    {
        Session::put('input', Input::all());

        if (Input::get('searchform') == 'advanced') {
            return Redirect::to('herrgardsdatabasen/sokresultat');
        } else {
            return Redirect::to('herrgardsdatabasen/sokresultat');
        }
    }

    protected function loadLandskap()
    {
        return Landskap::orderBy('namn')->get();
    }

    protected function loadHarad()
    {
        return Harad::orderBy('namn')->get();
    }

    protected function loadSocken()
    {
        return Socken::orderBy('namn')->get();
    }

    protected function loadStatus()
    {
        return Status::orderBy('namn')->get();
    }

    protected function loadJordnatur()
    {
        return Jordnatur::orderBy('namn')->get();
    }

    protected function loadAgarArr()
    {
        return DB::table('sandit_mansion_post')->
            select('ag_arr')->
            distinct()->
            whereNotNull('ag_arr')->
            orderBy('ag_arr')->
            get();
    }

    protected function loadTitle($select_field)
    {
        return Person::getTitles($select_field);
    }
    
    public function onSelectLandskap()
    {
        $landskap = Input::get('landskap');
        
        if (Input::get('searchform') == 'advanced') {

            if ($landskap == 0) {
                    return [
                        '#harad' => '',
                        '#socken' => ''
                    ];
                
            } else {
                $harad = Harad::where('landskap_id', $landskap)->orderBy('namn')->get();
                $socken = Landskap::find($landskap)->socken()->orderBy('namn')->get();
                return [
                    '#harad' => $this->renderPartial('@harad-select', ['harad' => $harad]),
                    '#socken' => $this->renderPartial('@socken-select', ['socken' => $socken])
                ];
            }
        } else {
            if ($landskap == 0) {
                return [
                    '#harad-simple' => '',
                    '#socken-simple' => ''
                ];
            
        } else {
            $harad = Harad::where('landskap_id', $landskap)->orderBy('namn')->get();
            return [
                '#harad-simple' => $this->renderPartial('@harad-select', ['harad' => $harad])
            ];
        }
        }
    }

    public function onSelectHarad()
    {
        $harad = Input::get('harad');

        if (Input::get('searchform') == 'advanced') {
            if ($harad == 0) {
                return [
                    '#socken' => ''
                ];
            } else {
                $socken = Socken::where('harad_id', $harad)->orderBy('namn')->get();
                
                return [
                    '#socken' => $this->renderPartial('@socken-select', ['socken' => $socken])
                ];
            } 

        } else {
            if ($harad == 0) {
                return [
                    '#socken-simple' => ''
                ];
            } else {
                $socken = Socken::where('harad_id', $harad)->orderBy('namn')->get();
                
                return [
                    '#socken-simple' => $this->renderPartial('@socken-select', ['socken' => $socken])
                ];
            } 
        }
    }
}
