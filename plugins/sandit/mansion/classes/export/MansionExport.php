<?php namespace Sandit\Mansion\Classes\Export;

use Sandit\Mansion\Models\Gard;
use Sandit\Mansion\Models\Person;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;


class MansionExport implements FromCollection, WithHeadings
{
    const EXTENSION = 'xls';

    protected $gard_id;

    protected $excel_columns = array(
        'Gård'=>'gard',
        'Socken'=>'socken',
        'Härad'=>'harad',
        'Landskap'=>'landskap',
        'År början'=>'ar_borjan',
        'År början anm'=>'ar_borjan_anm',
        'År slut'=>'ar_slut',
        'År slut anm'=>'ar_slut_anm',
        'Status'=>'status',
        'Jordnatur'=>'jordnatur',
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
        'Storlek herrgård mtl'=>'storlek_herrgard_mtl',
        'Storlek har'=>'storlek_har',
        'Storlek åker har'=>'storlek_aker_har',
        'Gods mtl'=>'gods_mtl',
        'Gods har'=>'gods_har',
        'Gods åker har'=>'gods_aker_har',
        'Taxering'=>'taxering',
        'Brukareförhållande' => 'brukareforhallande',
        'Kommentar' => 'kommentar',
        'Källa'=>'kalla'
    );

    public function __construct($gard_id)
    {
        $this->gard_id = $gard_id;
    }

    public function headings(): array
    {
        return array_keys($this->excel_columns);
    }

    public function collection()
    { 
        $gard = Gard::getGardPosts($this->gard_id);

        foreach($gard->poster as $post) {
            $rows[] = [
                $gard->namn,
                $gard->socken->namn,
                $gard->socken->harad->namn,
                $gard->socken->harad->landskap->namn,
                $post->ar_borjan,
                $post->ar_borjan_anm,
                $post->ar_slut,
                $post->ar_slut_anm,
                is_null($post->status) ? null : ucfirst($post->status->namn),
                $this->makeJordnaturString($post->jordnatur),
                ucfirst($post->ag_arr),
                $post->typ,
                is_null($post->agare) ? null : ucfirst($post->agare->titel_tjanst),
                is_null($post->agare) ? null : ucfirst($post->agare->titel_familj),
                is_null($post->agare) ? null : $post->agare->namn,
                is_null($post->agare) ? null : $post->agare->efternamn,
                is_null($post->maka1) ? null : ucfirst($post->maka1->titel_tjanst),
                is_null($post->maka1) ? null : ucfirst($post->maka1->titel_familj),
                is_null($post->maka1) ? null : $post->maka1->namn,
                is_null($post->maka1) ? null : $post->maka1->efternamn,
                is_null($post->maka2) ? null : ucfirst($post->maka2->titel_tjanst),
                is_null($post->maka2) ? null : ucfirst($post->maka2->titel_familj),
                is_null($post->maka2) ? null : $post->maka2->namn,
                is_null($post->maka2) ? null : $post->maka2->efternamn,
                $post->storlek_herrgard_mtl,
                $post->storlek_har,
                $post->storlek_aker_har,
                $post->gods_mantal,
                $post->gods_hektar,
                $post->gods_aker_hektar,
                $post->taxering,
                $post->brukareforhallande,
                $post->kommentar,
                $post->kalla->namn
            ];
        }
        return collect($rows);
    }

    public function makeFileName(): string
    {
        $gard = Gard::find($this->gard_id);
        $file_name = str_replace(' ', '', ucwords($gard->namn));
        $file_name .= str_replace(' ', '', ucwords($gard->socken->harad->landskap->namn));
        $file_name .= str_replace(' ', '', ucwords($gard->socken->harad->namn));
        $file_name .= str_replace(' ', '', ucwords($gard->socken->namn));

        $file_name = $this->replaceSwedishLettersWithAscii($file_name);
        $file_name = preg_replace('~[^-\w]+~', '', $file_name);
        
        return $file_name.'.'.self::EXTENSION;
    }

    private function replaceSwedishLettersWithAscii(string $string): string
    {
        $RemoveChars = ['/å/','/ä/','/ö/','/Å/','/Ä/','/Ö/'];
        $ReplaceWith = ['a','a','o','A','A','O'];

        $text  = preg_replace($RemoveChars, $ReplaceWith, $string);

        return $text;
    }

    private function makeJordNaturString($jordnatur) 
    {
        $string = '';

        foreach ($jordnatur as $jn) {    
            $string .= $jn->namn.', ';
        }
        return trim($string, ', ');
    }
}