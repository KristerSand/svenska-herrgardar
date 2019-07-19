<?php namespace Sandit\Mansion\Classes\Export;

use App\User;
use Maatwebsite\Excel\Concerns\FromCollection;

class MansionExport implements FromCollection
{
    public function collection()
    {
        return User::all();
    }
}