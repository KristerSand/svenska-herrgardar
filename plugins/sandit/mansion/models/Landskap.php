<?php namespace Sandit\Mansion\Models;

use Model;
use DB;


/**
 * Model
 */
class Landskap extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;
    protected $fillable = array('namn');

    /**
     * @var array Validation rules
     */
    public $rules = [
        'namn'                  => 'required'
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'sandit_mansion_landskap';

    protected $hidden = [
        'id',
    ];


    public $hasMany = [
        'harad' => 'Sandit\Mansion\Models\Harad'
    ];

    public $hasManyThrough = [
        'socken' => [
            'Sandit\Mansion\Models\Socken',
            'through' => 'Sandit\Mansion\Models\Harad'
        ],
    ];


    public function socknar() {
        return $this->hasManyThrough('socken', 'harad');
    }

    public function get_landskap_id($param)
    {
        $query = 'SELECT id
                    FROM sandit_mansion_landskap
                    WHERE l.namn=?';
        $result = DB::select($query,$param['landskap']);

        if( ! empty($result)) {
            $first = array_shift($result);
            return $first->id;
        }
        return DB::table('sandit_mansion_landskap')->insertGetId(array('namn' => $param['landskap']));
    }
}
