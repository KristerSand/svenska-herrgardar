<?php

use Sandit\Mansion\Classes\Repositories\SearchRepositoryInterface;
use Sandit\Mansion\Models\Gard;
use Sandit\Mansion\Classes\Export\MansionExport;
use Vdomah\Excel\Classes\Excel;

Route::group(['prefix' => 'api/v1', 'middleware' => ['\Barryvdh\Cors\HandleCors']], function(){

        Route::get('gard', function() {
            $ids = [];
            $id_type = '';
            $relations = [];

            if (Input::has('id')) {
                $ids = explode(',', Input::get('id'));
                $id_type = 'id';
            } elseif (Input::has('toraid')) {
                $ids = explode(',', Input::get('toraid'));
                $id_type = 'toraid';
            }
            if (Input::has('with')) {
                $relations = explode(',', Input::get('with'));
            }            
            $search_repo = App::make('SearchRepositoryInterface');
            return $search_repo->getGardar($ids, $id_type, $relations);
        });

        Route::get('gard/{id}', function($id) {
            $relations = [];

            if (Input::has('with')) {
                $relations = explode(',', Input::get('with'));
            }
            $search_repo = App::make('SearchRepositoryInterface');
            return $search_repo->getGardar([$id], 'id', $relations);
        });
});

Route::get('downloadExcel/{id}', function($id) {
    $mansion_exporter = new MansionExport($id);
    $file_name = $mansion_exporter->makeFileName();

    //dd($mansion_exporter->gard_id, $file_name);

    return Excel::excel()->download($mansion_exporter, $file_name);
});