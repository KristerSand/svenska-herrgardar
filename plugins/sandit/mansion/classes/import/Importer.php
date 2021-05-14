<?php namespace Sandit\Mansion\Classes\Import;

use Sandit\Mansion\Classes\Repositories\ImportRepositoryInterface;

abstract class Importer
{
    private $message = array('message' => '', 'message_type' => '');

    abstract protected function isPostExisting($post_data);
    abstract protected function isIdExisting($post_data);
    abstract protected function addPost($post_data);

    public function __construct(ImportRepositoryInterface $repository, $import)
    {
        $this->repository = $repository;
        $this->import = $import;
    }

    /**
     * @param array $data_array
     * @return void
     */
    public function doImport(array $data_array)
    {
        $nr_of_saved_posts = 0;
        $required_posts = array();
        $duplicate_posts = array();
        $duplicate_gard_ids = array();
        $total_nr_of_posts = 0;
        $file_name = $this->import->getExcelFileName();
        $column_error_message = $this->checkColumns($data_array);
        
        if ( '' !== $column_error_message ) {
            $this->message = [
                'message_type' => 'error',
                'message' => $column_error_message . ", i excelarket '" . $file_name . "'."
            ];
            $this->repository->deleteImport($this->import->id);
            return;
        }
        foreach ($data_array as $index => $data) {
            
            $data = array_map('trim', $data);
            $dbData = $this->replaceEmptyFieldsWithNull($data);

            if ($this->isEmptyRow($dbData)) {
                continue;
            }
            $total_nr_of_posts++;

            if ($this->isRequiredFieldsMissing($dbData)) {
                $required_posts[$index+2] = $data;
            } elseif ($this->isPostExisting($dbData)) {
                $duplicate_posts[$index+2] = $data;
            } elseif ($this->isIdExisting($dbData)) {
                $duplicate_gard_ids[$index+2] = $data; 
            } else {
                $dbData['import_id'] = $this->import->id;
                try {
                    $this->addPost($dbData);
                } catch (\Illuminate\Database\QueryException $e) {
                    $this->repository->deleteImportAndAllItsData($this->import->id);
                    $this->message = [
                        'message_type' => 'error',
                        'message' =>
                        'Importen avbröts på rad '.($index+2).', troligtvis på grund data som inte kan sparas.<br />
                        Felmeddelande: '.$e->getMessage().'<br />Ingen data sparades.'
                    ];
                    return;
                }
                $nr_of_saved_posts++;
            }
        }
        if ($nr_of_saved_posts > 0) {
            $this->repository->updateImport(
                $this->import->id,
                $nr_of_saved_posts,
                $total_nr_of_posts
            );
        } else {
            $this->repository->deleteImport($this->import->id);
        }
        $this->message = $this->createMessage(
                    $total_nr_of_posts,
                    $nr_of_saved_posts,
                    $duplicate_posts,
                    $duplicate_gard_ids,
                    $required_posts
        );
    }

    public function getMessage()
    {
        return $this->message;
    }


    /**** VALIDATION ****/

    /**
     * @param array $posts_data
     * @return string
     */
    private function checkColumns(array $posts_data) : string
    {
        $import_keys = array_keys($posts_data[0]);
        $keys = array_values($this->excel_columns);
        $compare_keys = $import_keys;

        foreach ($import_keys as $key => $i_key) {

            if( in_array($i_key, $keys)) {
                unset($compare_keys[$key]);
                $c_key = array_search($i_key, $keys);
                unset($keys[$c_key]);
            } elseif (empty($keys) && ! empty($compare_keys)) {
                return 'För många kolumner: '.implode(', ', $compare_keys);
            } else {
                return 'Fel kolumnnamn: '.$i_key;
            }
        }
        if ( ! empty($keys)) {
            return 'Kolumner saknas: '.implode(', ', $keys);
        }
        return '';
    }

    /**
     * @param array $row
     * @return bool
     */
    private function isRequiredFieldsMissing(array $row) : bool
    {
        foreach ($this->required_fields as $field) {

            if (empty($row[$field])) {
                return true;
            }
        }
        return false;
    }


    /**** RESULT MESSAGE ****/

    /**
     * @param int     total_nr_of_posts
     * @param int     $nr_of_saved_posts
     * @param array   $duplicate_posts
     * @param array   $required_posts
     * @return array
     */
    private function createMessage(
        int $total_nr_of_posts, 
        int $nr_of_saved_posts, 
        array $duplicate_posts,
        array $duplicate_gard_ids,  
        array $required_posts
        ) : array
    {
        $file_name = $this->import->getExcelFileName();

        if ($nr_of_saved_posts > 0) {
            $message['message_type'] = 'success';
            $message['message'] = "Importen av '$file_name' är klar.<br /><br />".
                $nr_of_saved_posts.' poster av '.$total_nr_of_posts.' sparade.' .
                $this->getExtraInformation($required_posts, $duplicate_posts, $duplicate_gard_ids);
        } else {
            $message['message_type'] = 'success';

            if (empty($required_posts)) {
                $message['message'] = "Det finns ingen ny data i '$file_name'. 0 poster sparade.";
            } else {
                $message['message'] = '0 poster sparade.'.
                    $this->getExtraInformation($required_posts, $duplicate_posts, $duplicate_gard_ids);
            }
        }
        return $message;
    }

    /**
     * @param array $required_posts
     * @param array $duplicate_posts
     * @return string
     */
    private function getExtraInformation(
        array $required_posts,
        array $duplicate_posts,
        array $duplicate_gard_ids
        ) : string
    {
        $message = '';

        if ( ! empty($required_posts)) {
            $message .= '<br /><br />';
            $message .= count($required_posts).' poster saknar obligatoriska värden. Rad:<br />'
            .implode(',', array_keys($required_posts));
        }
        if ( ! empty($duplicate_gard_ids)) {
            $message .= '<br /><br />';
            $message .= count($duplicate_gard_ids).' poster har duplicerade löpnummer. Rad:<br />'
            .implode(',',array_keys($duplicate_gard_ids));
        }
        if ( ! empty($duplicate_posts)) {
            $message .= '<br /><br />';
            $message .= count($duplicate_posts).' poster är dubletter. Rad:<br />'
            .implode(',',array_keys($duplicate_posts));
        }
        
        return $message;
    }


    /**
     * @param array $post_data
     * @return array
     */
    private function replaceEmptyFieldsWithNull(array $post_data) : array
    {
        return array_map(function($value) {
               return $value === "" ? NULL : $value;
            }, $post_data);
    }

    /**
     * @param array dbData
     * @return bool
     */
    private function isEmptyRow(array $dbData) : bool
    {
        foreach($dbData as $item) {
            if ( ! empty($item)) {
                return false;
            }
        }
        return true;
    }
}
