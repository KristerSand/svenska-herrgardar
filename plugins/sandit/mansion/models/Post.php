<?php namespace Sandit\Mansion\Models;

use Model;

/**
 * Model
 */
class Post extends Model
{
    use \October\Rain\Database\Traits\Validation;
    //use \October\Rain\Database\Traits\Nullable;

    protected $guarded = [];
    protected $hidden = [
        'id',
        'gard_id',
        'status_id',
        'agare_person_id',
        'maka1_person_id',
        'maka2_person_id',
        'kalla_id',
        'import_id',
        'created_at',
        'updated_at'
    ];

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'sandit_mansion_post';

    public $belongsTo = [
        'gard'   => ['Sandit\Mansion\Models\Gard'],
        'agare'  => ['Sandit\Mansion\Models\Person', 'key' => 'agare_person_id'],
        'maka1'  => ['Sandit\Mansion\Models\Person', 'key' => 'maka1_person_id'],
        'maka2'  => ['Sandit\Mansion\Models\Person', 'key' => 'maka2_person_id'],
        'status' => ['Sandit\Mansion\Models\Status'],
        'kalla'  => ['Sandit\Mansion\Models\Kalla'],
        'import' => ['Sandit\Mansion\Models\Import'],
    ];

    public $belongsToMany = [
        'jordnatur' => ['Sandit\Mansion\Models\Jordnatur','table' => 'sandit_mansion_jordnatur_post']
    ];
}