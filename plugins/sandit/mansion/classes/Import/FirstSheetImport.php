<?php namespace Sandit\Mansion\Classes\Import;

use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class FirstSheetImport implements ToArray, WithHeadingRow
{

    public function __construct(Importer $importer)
    {
        $this->importer = $importer;
    }
    
    /**
     * @param array $array
     * @return void
     */
    public function array(array $array)
    {
        return $this->importer->doImport($array);
    }
}
