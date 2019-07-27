<?php namespace Sandit\Mansion\Classes\Import;

ini_set('memory_limit', '3000M');
ini_set('max_execution_time', '0');

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Sandit\Mansion\Classes\Import\FirstSheetImport;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PostImport implements WithMultipleSheets
{
    public function __construct(Importer $importer)
    {
        $this->importer = $importer;
    }

    public function sheets(): array
    {
        return [
            new FirstSheetImport($this->importer)
        ];
    }

     /**
     * @param array $array
     * @return void
     */
    /*public function array(array $array)
    {
        return $this->importer->doImport($array);
    }*/
}
