<?php namespace Sandit\Mansion\Models;

use Model;
use Input;
use Auth;
use Validator;
use ValidationException;
use BackendAuth;

class Import extends Model
{
    use \October\Rain\Database\Traits\Validation;

    protected $guarded = [];

    /**
     * @var array Validation rules
     */
    public $rules = [
      'importfile' => 'required',
    ];


    private $upload_dir = 'uploads/public/';

    /**
     * @var string The database table used by the model.
     */
    public $table = 'sandit_mansion_import';

    public $attachOne = ['importfile' => 'System\Models\File'];

    public $hasMany = ['post' => 'Sandit\Mansion\Models\Post'];

    public $hasOne = ['user' => 'Backend\Models\User'];


    public function beforeSave()
    {
        $user = BackendAuth::getUser();
        $this->user_id = $user->id;
    }

    public function getExcelFilePath()
    {
        $absolute_path = $this->importfile->getLocalPath();
        $relpath = substr($absolute_path, strpos($absolute_path, $this->upload_dir));
        return $relpath;
    }

    public function getExcelFileName()
    {
        return $this->importfile->file_name;
    }
}
