<?php namespace Sandit\Mansion\Classes;

use Sandit\Mansion\Classes\Importer;

class EstateImporter extends Importer
{
    //const IMPORT_NAME = 'Gods';

    protected $required_fields = array(
        'Gård'=>'gard',
        'Socken'=>'socken',
        'Härad'=>'harad',
        'Landskap'=>'landskap',
        'År'=>'ar',
        'Källa'=>'kalla',
        'Herrgård'=>'herrgard',
        'Socken herrgård'=>'socken_herrgard',
        'Härad herrgård'=>'harad_herrgard',
        'Landskap herrgård'=>'landskap_herrgard'
        );


    protected $excel_columns = array(
        'Gård'=>'gard',
        'Socken'=>'socken',
        'Härad'=>'harad',
        'Landskap'=>'landskap',
        'Nr'=>'nr',
        'Jordnatur'=>'jordnatur',
        'Mtl'=>'mtl',
        'Brukareförhållande (siffra=bonde)'=>'brukareforhallande_siffrabonde',
        'År'=>'ar',
        'Ägare (titel tjänst)'=>'agare_titel_tjanst',
        'Ägare (titel familj)'=>'agare_titel_familj',
        'Ägare (förnamn)'=>'agare_fornamn',
        'Ägare (efternamn)'=>'agare_efternamn',
        'Kommentar'=>'kommentar',
        'Herrgård'=>'herrgard',
        'Socken (herrgård)'=>'socken_herrgard',
        'Härad (herrgård)'=>'harad_herrgard',
        'Landskap (herrgård)'=>'landskap_herrgard',
        'Källa' => 'kalla',
        );


    protected function addPost($post_data)
    {
        $this->repository->addEstatePost($post_data);
    }


    protected function isPostExisting($post_data)
    {
        return $this->repository->isEstatePostExisting($post_data);
    }
}
