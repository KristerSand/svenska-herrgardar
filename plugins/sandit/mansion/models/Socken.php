<?php namespace Sandit\Mansion\Models;

use Model;
use DB;

/**
 * Model
 */
class Socken extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;

    /**
     * @var array Validation rules
     */
    public $rules = [
        'namn'                  => 'required',
        'harad'                 => 'required'
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'sandit_mansion_socken';

    protected $hidden = [
        'id',
        'harad_id'
    ];


    public $belongsTo = [
        'harad' => ['Sandit\Mansion\Models\Harad','order' => 'namn']
    ];

    public $hasMany = [
        'gard' => 'Sandit\Mansion\Models\Gard'
    ];

    public $hasOneThrough = [
        'landskap' => ['Sandit\Mansion\Models\Landskap', 'Sandit\Mansion\Models\Harad'],
    ];


    public function get_socken_id($param)
    {
        $query = 'SELECT s.id
                    FROM sandit_mansion_socken s JOIN sandit_mansion_harad h ON s.harad_id=h.id JOIN sandit_mansion_landskap l ON h.landskap_id=l.id
                    WHERE s.namn=? AND h.namn=? AND l.namn=?';
        $result = DB::select($query,array($param['socken'], $param['harad'], $param['landskap']));

        if( ! empty($result)) {
            return $result[0]->id;
        }
        $socken = $param['socken'];
        $harad = new Harad;
        $harad_id = $harad->get_harad_id($param);

        return DB::table('sandit_mansion_socken')->insertGetId(array('namn' => $socken, 'harad_id' => $harad_id));
    }
}
