<?php namespace Sandit\Mansion\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Sandit\Mansion\Classes\Repositories\ImportRepositoryInterface;
use Sandit\Mansion\Classes\Import\MansionImporter;
use Sandit\Mansion\Classes\Import\PostImport;
use Maatwebsite\Excel\Facades\Excel;
use Input;
use Request;
use Redirect;
use Flash;
use App;
use Session;
use Exception;

class Import extends Controller
{
    public $implement = [
        'Backend\Behaviors\ListController',
        'Backend\Behaviors\FormController'
    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';


    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Sandit.Mansion', 'mansion', 'import');

        $this->vars['message'] = Session::get('message');
        $this->vars['message_type'] = Session::get('message_type');
    }


    public function formAfterSave($model)
    {
        $repository = App::make('ImportRepositoryInterface');   
        $importer = new MansionImporter($repository, $model);
        $postImport = new PostImport($importer);
        
        try {
            Excel::import($postImport, $model->getExcelFilePath());
            $message = $importer->getMessage();
        } catch (\Exception $e) {
            $message = ['message' => $e->getMessage(), 'message_type' => 'error'];
        }
        Session::flash('message', $message['message']);
        Session::flash('message_type', $message['message_type']);
    }


    public function onDelete()
    {
        $import_ids = Input::get('checked');
        $repository = App::make('ImportRepositoryInterface');

        foreach ($import_ids as $import_id) {
            $repository->deleteImportAndAllItsData($import_id);
        }
        $message = $this->getDeleteMessage($import_ids);
        Flash::success($message);
        return $this->listRefresh();
    }


    private function getDeleteMessage($import_ids)
    {
        $message = count($import_ids) > 1 ? 'Importerna' : 'Importen';
        $message .= ' har raderats med alla tillhörande poster.';
        return $message;
    }
}
