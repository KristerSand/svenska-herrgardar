<?php namespace Sandit\Mansion\Models;

use Model;
use DB;

/**
 * Model
 */
class Status extends Model
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
    public $table = 'sandit_mansion_status';

    protected $hidden = [
        'id'
    ];

    public $hasMany = [
        'post' => ['Sandit\Mansion\Models\Post']
    ];


    public static function findMostFrequentStatus($statuses) : string
    {
        if (is_null($statuses) || empty($statuses)) {
            return '';
        }
        $status_array = array_count_values(explode(',', $statuses));
        arsort($status_array);

        if (count($status_array) === 1) {
            return ucfirst(key($status_array));
        } 
        $standsgard = 'Ståndsgård';
        $herrgard = 'Herrgård';
        $herrgard_frequence = true === key_exists($herrgard, $status_array) ? $status_array[$herrgard] : 0;
        $standsgard_frequence = true === key_exists($standsgard, $status_array) ? $status_array[$standsgard] : 0;

        if ($standsgard_frequence === 0 && $herrgard_frequence === 0) {
            return ucfirst(key($status_array));
        }
        if ($standsgard_frequence > $herrgard_frequence) {
            return $standsgard;
        } else {
            return $herrgard;
        }
    }


    public function alterStatus($input, $status_id)
    {
        $status = Status::find($status_id);

        if ($status->namn == $input['status']) {
            return $status_id;
        }
        $result = DB::table('sandit_mansion_status')->where('namn','=',$input['status'])->get();

        if ($result) {
            $existing_status = array_shift($result);
            return $existing_status->id;
        }
        $new_status = new Status;
        $new_status->namn = $input['status'];
        $new_status->save();
        return $new_status->id;
    }
}
