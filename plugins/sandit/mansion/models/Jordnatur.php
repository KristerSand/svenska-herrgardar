<?php namespace Sandit\Mansion\Models;

use Model;

/**
 * Model
 */
class Jordnatur extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;
    protected $fillable = array('namn');

    /**
     * @var string The database table used by the model.
     */
    public $table = 'sandit_mansion_jordnatur';

    public $hidden = [
        'id',
        'created_at',
        'updated_at',
        'pivot'
    ];

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $belongsToMany = [
        'post' => ['Sandit\Mansion\Models\Post', 'table' => 'sandit_mansion_jordnatur_post']
    ];
}
