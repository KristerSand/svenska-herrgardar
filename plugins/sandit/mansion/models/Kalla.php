<?php namespace Sandit\Mansion\Models;

use Model;
use DB;

/**
 * Model
 */
class Kalla extends Model
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
    public $table = 'sandit_mansion_kalla';

    protected $hidden = [
        'id'
    ];

    public $hasMany = [
        'post' => 'Sandit\Mansion\Models\Post'
    ];


    public function alterKalla($input, $kalla_id)
    {
        $kalla = Kalla::find($kalla_id);

        if ($kalla->namn == $input['kalla']) {
            return $kalla_id;
        }
        $result = DB::table('sandit_mansion_kalla')->where('namn','=',$input['kalla'])->get();

        if ($result) {
            $existing_kalla = array_shift($result);
            return $existing_kalla->id;
        }
        $new_kalla = new Kalla;
        $new_kalla->namn = $input['kalla'];
        $new_kalla->save();
        return $new_kalla->id;
    }
}
