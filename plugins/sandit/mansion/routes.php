<?php
ini_set('memory_limit', '3000M');
ini_set('max_execution_time', '0');
use Sandit\Mansion\Classes\Repositories\SearchRepositoryInterface;
use Sandit\Mansion\Models\Gard;
use Sandit\Mansion\Classes\Export\MansionExport;
use Maatwebsite\Excel\Facades\Excel;

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
            $offset = Input::get('offset', 0);
            $limit = Input::get('limit', 0);   
            $search_repo = App::make('SearchRepositoryInterface');
            return $search_repo->getGardar($ids, $id_type, $relations, $offset, $limit);
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

    return Excel::download($mansion_exporter, $file_name);
});