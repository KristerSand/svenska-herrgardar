<?php namespace Sandit\Mansion\Classes\Import;

use Sandit\Mansion\Classes\Import\Importer;

class MansionImporter extends Importer
{
    protected $required_fields = array(
        'Löpnummer' => 'lopnummer',
        'Gård'=>'gard',
        'Socken'=>'socken',
        'Härad'=>'harad',
        'Landskap'=>'landskap',
        'År början'=>'ar_borjan',
        'Källa'=>'kalla'
        );


    protected $excel_columns = array(
        'TORAId' => 'toraid',
        'Löpnummer' => 'lopnummer',
        'Gård'=>'gard',
        'Socken'=>'socken',
        'Härad'=>'harad',
        'Landskap'=>'landskap',
        'Jordnatur'=>'jordnatur',
        'Storlek herrgård mtl'=>'storlek_herrgard_mtl',
        'Storlek har'=>'storlek_har',
        'Storlek åker har'=>'storlek_aker_har',
        'Status'=>'status',
        'År början'=>'ar_borjan',
        'År början anm'=>'ar_borjan_anm',
        'År slut'=>'ar_slut',
        'År slut anm'=>'ar_slut_anm',
        'Äg/arr'=>'agarr',
        'Typ'=>'typ',
        'Titel tjänst'=>'titel_tjanst',
        'Titel familj'=>'titel_familj',
        'Namn'=>'namn',
        'Efternamn'=>'efternamn',
        'M 1 titel tjänst'=>'m_1_titel_tjanst',
        'M 1 titel familj'=>'m_1_titel_familj',
        'M 1 namn'=>'m_1_namn',
        'M 1 efternamn'=>'m_1_efternamn',
        'M 2 titel tjänst'=>'m_2_titel_tjanst',
        'M 2 titel familj'=>'m_2_titel_familj',
        'M 2 namn'=>'m_2_namn',
        'M 2 efternamn'=>'m_2_efternamn',
        'Gods mtl'=>'gods_mtl',
        'Gods har'=>'gods_har',
        'Gods åker har'=>'gods_aker_har',
        'Taxering'=>'taxering',
        'Källa'=>'kalla'
    );


    protected function addPost($post_data)
    {
        $this->repository->addMansionPost($post_data);
    }


    protected function isPostExisting($post_data)
    {
        return $this->repository->isMansionPostExisting($post_data);
    }

    protected function isIdExisting($post_data)
    {
        return $this->repository->isIdExisting($post_data);
    }

    public function getColumns() : array
    {
        return $this->excel_columns;
    }
}
