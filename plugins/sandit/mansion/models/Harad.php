<?php namespace Sandit\Mansion\Models;

use Model;
use DB;

/**
 * Model
 */
class Harad extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;

    protected $fillable = array('namn','landskap_id');

    /**
     * @var array Validation rules
     */
    public $rules = [
        'namn'                  => 'required',
        'landskap'              => 'required'
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'sandit_mansion_harad';

    protected $hidden = [
        'id',
        'landskap_id'
    ];


    public $belongsTo = [
        'landskap' => 'Sandit\Mansion\Models\Landskap',
        'order' => 'namn'
    ];

    public $hasMany = [
        'socken' => 'Sandit\Mansion\Models\Socken'
    ];



    public function get_harad_id($param)
    {
        $query = 'SELECT h.id
                    FROM sandit_mansion_harad h JOIN sandit_mansion_landskap l ON h.landskap_id=l.id
                    WHERE h.namn=? AND l.namn=?';
        $result = DB::select($query,array($param['harad'],$param['landskap']));

        if( ! empty($result)) {
            return $result[0]->id;
        }

        $landskap = Landskap::firstOrCreate(array('namn' => $param['landskap']));

        return DB::table('sandit_mansion_harad')->insertGetId(array('namn' => $param['harad'], 'landskap_id' => $landskap->id));
    }
}
