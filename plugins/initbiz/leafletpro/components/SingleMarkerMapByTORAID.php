<?php namespace Initbiz\LeafletPro\Components;

use Cms\Classes\ComponentBase;
use Initbiz\LeafletPro\Models\Marker;
use Input;

class SingleMarkerMapByTORAID extends ComponentBase
{
    use \Initbiz\LeafletPro\Traits\LeafletHelpers;

    public $centerLonLat;

    public $initialZoom;

    public $markers;

    public $scrollProtection;

    public function componentDetails()
    {
        return [
            'name'        => 'initbiz.leafletpro::lang.components.single_marker_map_by_toraid.name',
            'description' => 'initbiz.leafletpro::lang.components.single_marker_map_by_toraid.description'
        ];
    }

    public function defineProperties()
    {
        $properties = [
            'initialZoom' => [
                'title'             => 'initbiz.leafletpro::lang.components.zoom_title',
                'description'		=> 'initbiz.leafletpro::lang.components.zoom_description',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'initbiz.leafletpro::lang.components.zoom_validation_message',
                'default'			=> '12'
            ],
            'marker' => [
                'title'             => 'initbiz.leafletpro::lang.components.single_marker_map.marker_title',
                'description'       => 'initbiz.leafletpro::lang.components.single_marker_map.marker_description',
                'default'           => 'true',
                'type'              => 'dropdown',
            ],
            'findBy' => [
                'title'             => 'initbiz.leafletpro::lang.components.single_marker_map.find_by_title',
                'description'       => 'initbiz.leafletpro::lang.components.single_marker_map.find_by_description',
                'type'              => 'dropdown',
                'default'           => 'id',
                'options'           => [
                    'id'   => 'initbiz.leafletpro::lang.components.single_marker_map.find_by_id',
                    'name' => 'initbiz.leafletpro::lang.components.single_marker_map.find_by_name',
                ],
            ],
            'scrollProtection' => [
                'title'             => 'initbiz.leafletpro::lang.components.scroll_protection_title',
                'description'       => 'initbiz.leafletpro::lang.components.scroll_protection_description',
                'default'           => '1',
                'type'              => 'checkbox',
            ],
        ];

        return $properties;
    }

    public function onRun()
    {
        $this->addJs('assets/node_modules/leaflet/dist/leaflet.js');
        $this->addCss('assets/node_modules/leaflet/dist/leaflet.css');

        $toraid = Input::get("toraid");

        $marker = Marker::where("tora_id", $toraid)->first();
        $markers = [$marker];
        $this->markers = $markers;

        $this->scrollProtection = ($this->property('scrollProtection') === "0") ? 'enable' : 'disable';

        $this->initialZoom = $this->property('initialZoom');
        $this->centerLonLat = $marker->lat . ', ' . $marker->lon;
    }

    public function getMarkerOptions()
    {
        return Marker::published()->get()->pluck('name', 'id')->toArray();
    }
}
