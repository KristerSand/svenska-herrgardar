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


    public static function find_most_frequent($statuses)
    {
        if (empty($statuses)) {
                return '';
        }
        $tmp = array_count_values(explode(',', $statuses));
        arsort($tmp);
        $nr = reset($tmp);
        $name = Helper::mb_ucfirst(key($tmp));
        $next_nr = next($tmp);

        if ( ! $next_nr || $nr > $next_nr) {
            return $name;
        }
        if ($name == 'Ståndsgård' || $name == 'Herrgård') {
            return $name;
        }
        $next_name = Helper::mb_ucfirst(key($tmp));

        if ($next_name == 'Ståndsgård' || $next_name == 'Herrgård') {
            return $next_name;
        }
        return $name;
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
