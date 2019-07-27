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

    /*Validator::extend('foo', function($attribute, $value, $parameters) {
        return $value == 'foo';
    });*/

    /**
     * @var array Validation rules
     */
    public $rules = [
      //'importfile' => ['required', 'mimes:xls,xlsx'],
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


    /*public function afterValidate()
    {
      $importer = Importer::getImporter($this->import_type);
      //var_dump($importer);

      throw new ValidationException([
         'importfile' => var_export($importer,true)
      ]);
  }*/


    /*public function afterDelete()
    {
        $importRepository = new ImportRepository;
        $importRepository->delete($this->id, 'import');
    }*/
}
