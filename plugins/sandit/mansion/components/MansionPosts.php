<?php namespace Sandit\Mansion\Components;

use Cms\Classes\ComponentBase;
use Input;
use Redirect;
use Sandit\Mansion\Models\Gard;
use Sandit\Mansion\Models\Status;
use Sandit\Mansion\Classes\Export\MansionExport;
use Sandit\Mansion\Classes\suecia\Suecia;


class MansionPosts extends ComponentBase
{
    public $gard;
    public $format;
    public $status;
    public $suecia;

    public function componentDetails()
    {
        return [
            'name'        => 'MansionPosts',
            'description' => 'Lista med en gårds poster'
        ];
    }

    public function defineProperties()
    {
        return [
            'id' => [
                'title'       => 'Gårdens id',
                'description' => 'Gårdens id i databasen',
                'default'     => '{{ :id }}',
            ]
        ];
    }

    public function onRun()
    {
        $this->addJs("assets/node_modules/proj4/dist/proj4.js");
        $this->addJs("assets/node_modules/handlebars/dist/handlebars.js");
        $this->addJs("assets/tora.js");
        $this->format = Input::get('format');
        $gardId = $this->property('id');
        $this->gard = Gard::getGardPosts($gardId);
        $this->suecia = Suecia::hasSueciaImages($this->gard->toraid);
        $statuses  = [];
        foreach($this->gard->post as $post) {
            if($post->status) {
                $statuses[]=$post->status->namn;
            }
        }
        $statuses_text_list = implode(",",$statuses);
        $status = Status::findMostFrequentStatus($statuses_text_list);
        $this->status = $status;
    }
        

    
    public function onDownload()
    {
        $gardId = Input::get('id');
        return Redirect::to('downloadExcel/'.$gardId);
    }
}