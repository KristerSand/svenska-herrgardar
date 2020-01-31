<?php namespace Initbiz\LeafletPro\Components;

use Lang;
use Session;
use Cms\Classes\ComponentBase;
use Initbiz\LeafletPro\Models\Marker;
use Initbiz\LeafletPro\Exceptions\EmptyResponse;
use October\Rain\Exception\ApplicationException;

class MarkerMapFromResultSet extends ComponentBase
{
    use \Initbiz\LeafletPro\Traits\LeafletHelpers;

    public $centerLonLat;

    public $initialZoom;

    public $markers;

    public $scrollProtection;

    protected $pluginPropertySuffix = 'PluginEnabled';

    public function componentDetails()
    {
        return [
            'name'        => 'initbiz.leafletpro::lang.components.marker_map_from_resulset.name',
            'description' => 'initbiz.leafletpro::lang.components.marker_map_from_resulset.description'
        ];
    }

    public function defineProperties()
    {
        $properties = [
            'centerLonLat' => [
                'title'             => 'initbiz.leafletpro::lang.components.center_lon_lat',
                'description'		=> 'initbiz.leafletpro::lang.components.center_lon_lat_desc',
                'type'              => 'string',
                'default'			=> '51.505, -0.09'
            ],
            'initialZoom' => [
                'title'             => 'initbiz.leafletpro::lang.components.zoom_title',
                'description'		=> 'initbiz.leafletpro::lang.components.zoom_description',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'initbiz.leafletpro::lang.components.zoom_validation_message',
                'default'			=> '12'
            ],
            'scrollProtection' => [
                'title'             => 'initbiz.leafletpro::lang.components.scroll_protection_title',
                'description'       => 'initbiz.leafletpro::lang.components.scroll_protection_description',
                'default'           => '1',
                'type'              => 'checkbox',
            ],
            'getOverriding' => [
                'title'             => 'initbiz.leafletpro::lang.components.get_overriding_title',
                'description'       => 'initbiz.leafletpro::lang.components.get_overriding_description',
                'default'           => '0',
                'type'              => 'checkbox',
            ]
        ];

        return $properties + $this->getLeafletPluginsProperties();
    }

    public function onRun()
    {
        $leafletJs = [];
        $leafletCss = [];
        $activePlugins = [];

        $leafletJs[] = 'assets/node_modules/leaflet/dist/leaflet.js';
        $leafletCss[] = 'assets/node_modules/leaflet/dist/leaflet.css';

        foreach ($this->getLeafletPlugins() as $pluginCode => $pluginDef) {
            if ($this->property($pluginCode . $this->pluginPropertySuffix)) {
                $activePlugins[] = $pluginCode;
                $leafletJs[] = $pluginDef['jsPath'];
                $leafletCss[] = $pluginDef['cssPath'];
            }
        }

        $this->addJs($leafletJs);

        $this->addCss($leafletCss);

        $this->page['activeLeafletPlugins'] = $activePlugins;

        $initialParams = $this->getInitialParams();

        $this->centerLonLat = $initialParams['centerLonLat'];
        $this->initialZoom = $initialParams['initialZoom'];

        // Leaflet use scrollWheelZoom param, to it's negated scrollProtection
        $this->scrollProtection = ($this->property('scrollProtection') === "0") ? 'enable' : 'disable';
        $markers = array();
        if(Session::has("gard_resultset")) {
            //Loop through resultset in session and create markers.
            $gard_resultset = Session::pull("gard_resultset");
            
            foreach($gard_resultset as $gard) {
                if(isset($gard->id)) 
                {
                    $marker = Marker::where("gard_id", $gard->id)->first();
                    if($marker) {
                        $markers[] = $marker;
                    }
                    
                }
            }
        }
        
        $this->markers = $markers;
    }

    public function getInitialParams()
    {
        $result = [
            'centerLonLat' => $this->property('centerLonLat'),
            'initialZoom' => $this->property('initialZoom'),
        ];

        return $result;
    }

    /**
     * Makes properties definitions for Leaflet plugins, right now only checkboxes if enable the plugin for the component
     * @return array component properties definitions for this component
     */
    protected function getLeafletPluginsProperties()
    {
        $properties = [];

        foreach ($this->getLeafletPlugins() as $pluginCode => $pluginDef) {
            $property = [
                'title'         => $pluginDef['title'],
                'description'   => $pluginDef['description'],
                'type'          => 'checkbox',
                'group'         => 'initbiz.leafletpro::lang.components.leafletmap.plugins_group',
                'default'       => 0,
            ];

            $properties[$pluginCode . $this->pluginPropertySuffix] = $property;
        }

        return $properties;
    }

    /**
     * Registers Leaflet plugins to be used in the component
     * @return array Leaflet plugins
     */
    protected function getLeafletPlugins()
    {
        return [
            'markercluster' => [
                'title' => 'initbiz.leafletpro::lang.leafletmap_plugins.markercluster_name',
                'description' => 'initbiz.leafletpro::lang.leafletmap_plugins.markercluster_desc',
                'jsPath' => 'assets/node_modules/leaflet.markercluster/dist/leaflet.markercluster-src.js',
                'cssPath' => 'assets/node_modules/leaflet.markercluster/dist/MarkerCluster.css',
            ],
            'locatecontrol' => [
                'title' => 'initbiz.leafletpro::lang.leafletmap_plugins.locatecontrol_name',
                'description' => 'initbiz.leafletpro::lang.leafletmap_plugins.locatecontrol_desc',
                'jsPath' => 'assets/node_modules/leaflet.locatecontrol/dist/L.Control.Locate.min.js',
                'cssPath' => 'assets/node_modules/leaflet.locatecontrol/dist/L.Control.Locate.min.css',
            ]
        ];
    }
}
