<?php namespace Sandit\Mansion\Components;

use Cms\Classes\ComponentBase;
use Input;
use Sandit\Mansion\Models\Post;
use Sandit\Mansion\Models\Gard;
use Excel;
use Sandit\Mansion\Classes\Export\MansionExport;

class MansionPosts extends ComponentBase
{
    public $gard;

    public function componentDetails()
    {
        return [
            'name'        => 'MansionPosts',
            'description' => 'Lista av en gårds poster'
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
        $gardId = $this->property('id');
        $this->gard = Gard::getGardar($gardId, ['post'])->first();
    }

    public function onDownload()
    {
        $id = Input::get('id');
        $gardData = Gard::getGardar($id, ['post'])->first();
        return Excel::download($gardData , 'file.xlsx');
    }
}
