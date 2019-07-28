<?php namespace Sandit\Mansion;

use System\Classes\PluginBase;
use App;
use Illuminate\Foundation\AliasLoader;
use Config;



class Plugin extends PluginBase
{
    public function registerComponents()
    {
        return [
            'Sandit\Mansion\Components\SearchForm' => 'advanced_search',
            'Sandit\Mansion\Components\SearchResult' => 'search_result',
            'Sandit\Mansion\Components\MansionPosts' => 'mansion_posts',
        ];
    }

    public function registerSettings()
    {
    }

    public function register()
    {
        App::bind('ImportRepositoryInterface', 'Sandit\Mansion\Classes\Repositories\ImportRepository');
        App::bind('SearchRepositoryInterface', 'Sandit\Mansion\Classes\Repositories\SearchRepository');
    }

    public function boot()
    {
    }
}
