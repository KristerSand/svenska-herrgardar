<?php

use Sandit\Mansion\Classes\Repositories\SearchRepositoryInterface;
  
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
