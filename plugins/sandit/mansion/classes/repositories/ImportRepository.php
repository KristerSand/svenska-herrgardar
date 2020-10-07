<?php namespace Sandit\Mansion\Classes\Repositories;

use DB;
use Sandit\Mansion\Classes\Repositories\ImportRepositoryInterface;
use Sandit\Mansion\Models\Import;
use Sandit\Mansion\Models\Status;
use Sandit\Mansion\Models\Gard;
use Sandit\Mansion\Models\Kalla;
use Sandit\Mansion\Models\Post;
use Sandit\Mansion\Models\Person;
use Sandit\Mansion\Models\Jordnatur;


class ImportRepository implements ImportRepositoryInterface
{
	public function addMansionPost(array $post_data)
    {
        $post_id = $this->savePost($post_data);        
        $this->savePerson($post_data, 'person', $post_id);
        $this->savePerson($post_data, 'maka1', $post_id);
        $this->savePerson($post_data, 'maka2', $post_id);
        $this->saveStorlek($post_data, 'herrgard', $post_id);
        $this->saveJordnatur($post_data, $post_id);
    }


	public function addEstatePost(array $post_data)
    {
        $post_id = $this->savePost($post_data);
        $this->savePerson($post_data, 'person_estate', $post_id);
        $this->saveStorlek($post_data, 'gard', $post_id);
    }


	public function isMansionPostExisting(array $post_data) : bool
    {
        extract($post_data);
        
        $result = DB::table('sandit_mansion_post AS p')
            ->leftJoin('sandit_mansion_kalla AS k','k.id','=','p.kalla_id')
            ->leftJoin('sandit_mansion_status AS st','st.id','=','p.status_id')
            ->leftJoin('sandit_mansion_gard AS g','g.id','=','p.gard_id')
            ->leftJoin('sandit_mansion_person AS agare','agare.id','=','p.agare_person_id')
            ->leftJoin('sandit_mansion_person AS maka1','maka1.id','=','p.maka1_person_id')
            ->leftJoin('sandit_mansion_person AS maka2','maka2.id','=','p.maka2_person_id')
            ->where('g.id', $lopnummer)
            ->where('p.ar_borjan', $ar_borjan)
            ->where('p.ar_borjan_anm', $ar_borjan_anm)
            ->where('p.ar_slut', $ar_slut)
            ->where('p.ar_slut_anm', $ar_slut_anm)
            ->where('p.ag_arr', $agarr)
            ->where('p.typ', $typ)
            ->where('k.namn', $kalla)
            ->where('st.namn', $status)
            ->where('agare.titel_tjanst', $titel_tjanst)
            ->where('agare.titel_familj', $titel_familj)
            ->where('agare.namn', $namn)
            ->where('agare.efternamn', $efternamn)
            ->where('maka1.titel_tjanst', $m_1_titel_tjanst)
            ->where('maka1.titel_familj', $m_1_titel_familj)
            ->where('maka1.namn', $m_1_namn)
            ->where('maka1.efternamn', $m_1_efternamn)
            ->where('maka2.titel_tjanst', $m_2_titel_tjanst)
            ->where('maka2.titel_familj', $m_2_titel_familj)
            ->where('maka2.namn', $m_2_namn)
            ->where('maka2.efternamn', $m_2_efternamn)
            ->get();

        return $result->isNotEmpty();
    }


    private function isJordnaturEqual(array $jordnatur, Collection $queryResult) : bool
    {
        if ( ! is_null($jordnatur)) {
            $jordnaturFromImport = $this->splitJordnaturString($jordnatur);
        }  else {
            $jordnaturFromImport = [];
        }
        foreach ($result as $post) {
            $jordnaturFromDb = DB::table('sandit_mansion_jordnatur AS j')
            ->leftJoin('sandit_mansion_jordnatur_post AS pj','j.id','=','pj.jordnatur_id')
            ->select('namn')
            ->where('pj.post_id', '=', $post->id)
            ->get();

            if ($jordnaturFromDb->count() !== count($jordnaturFromImport)) {
                return false;
            }
            foreach($jordnaturFromImport as $jn) {

                if ($jordnaturFromDb->contains($jn) === false) {
                    return false;
                }
            }
        }
        return true;
    }


	public function isEstatePostExisting(array $post_data) : bool
    {
        extract($post_data);
        $result = DB::table('sandit_mansion_post AS p')
            ->leftJoin('sandit_mansion_kalla AS k','k.id','=','p.kalla_id')
            ->leftJoin('sandit_mansion_gard AS g','g.id','=','p.gard_id')
            ->leftJoin('sandit_mansion_socken AS s','s.id','=','g.socken_id')
            ->leftJoin('sandit_mansion_harad AS h','h.id','=','s.harad_id')
            ->leftJoin('sandit_mansion_landskap AS l','l.id','=','h.landskap_id')
            ->leftJoin('sandit_mansion_person AS pe','pe.id','=','p.agare_person_id')
            ->leftJoin('sandit_mansion_gard AS g2','g2.id','=','g.tillhor_herrgard')
            ->leftJoin('sandit_mansion_socken AS s2','s2.id','=','g2.socken_id')
            ->leftJoin('sandit_mansion_harad AS h2','h2.id','=','s2.harad_id')
            ->leftJoin('sandit_mansion_landskap AS l2','l2.id','=','h2.landskap_id')
			->leftJoin('sandit_mansion_jordnatur_post AS pj','p.id','=','pj.post_id')
			->leftJoin('sandit_mansion_jordnatur AS j','j.id','=','pj.jordnatur_id')
            ->where('g.namn', $gard)
            ->where('s.namn', $socken)
            ->where('h.namn', $harad)
            ->where('l.namn', $landskap)
			->where('j.namn', $jordnatur)
            ->where('g.nummer', $nr)
            ->where('p.storlek_herrgard_mtl', $mtl)
            ->where('p.brukareforhallande', $brukareforhallande_siffrabonde)
            ->where('p.ar_borjan', $ar)
            ->where('pe.titel_tjanst', $agare_titel_tjanst)
            ->where('pe.titel_familj', $agare_titel_familj)
            ->where('pe.namn', $agare_fornamn)
            ->where('pe.efternamn', $agare_efternamn)
            ->where('p.kommentar', $kommentar)
            ->where('g2.namn', $herrgard)
            ->where('s2.namn', $socken_herrgard)
            ->where('h2.namn', $harad_herrgard)
            ->where('l2.namn', $landskap_herrgard)
            ->where('k.namn', $kalla)
            ->get();

        return $result->isNotEmpty();
    }


    public function updateImport(int $import_id, int $saved_rows, int $total_rows)
    {
        $import = Import::find($import_id);
        $import->saved_rows = $saved_rows;
        $import->total_rows = $total_rows;
        $import->file_name = $import->getExcelFileName();
        $import->save();
    }


	public function deleteImport(int $import_id)
	{
        $import = Import::find($import_id);
        
        if ( ! is_null($import)) {
            $import->delete();
        }
	}


    /*public function deleteImportAndAllItsData(int $import_id): void
    {
		$this->deleteImport($import_id);
		Post::where('import_id', $import_id)->delete();
        DB::delete('DELETE s FROM sandit_mansion_status s LEFT JOIN sandit_mansion_post p ON s.id=p.status_id WHERE p.status_id IS NULL');
        DB::delete('DELETE k FROM sandit_mansion_kalla k LEFT JOIN sandit_mansion_post p ON k.id=p.kalla_id WHERE p.kalla_id IS NULL');
        DB::delete('DELETE i FROM sandit_mansion_import i LEFT JOIN sandit_mansion_post p ON i.id=p.import_id WHERE p.import_id IS NULL');
        DB::delete('DELETE g FROM sandit_mansion_gard g LEFT JOIN sandit_mansion_post p ON g.id=p.gard_id WHERE p.gard_id IS NULL AND g.tillhor_herrgard IS NOT NULL');
        DB::delete('DELETE g1 FROM sandit_mansion_gard g1 LEFT JOIN sandit_mansion_post p ON g1.id=p.gard_id LEFT JOIN sandit_mansion_gard g2 ON g1.id=g2.tillhor_herrgard WHERE p.gard_id IS NULL AND g2.tillhor_herrgard IS NULL');
        DB::delete('DELETE s FROM sandit_mansion_socken s LEFT JOIN sandit_mansion_gard g ON s.id=g.socken_id WHERE g.socken_id IS NULL');
        DB::delete('DELETE h FROM sandit_mansion_harad h LEFT JOIN sandit_mansion_socken s ON h.id=s.harad_id WHERE s.harad_id IS NULL');
        DB::delete('DELETE l FROM sandit_mansion_landskap l LEFT JOIN sandit_mansion_harad h ON l.id=h.landskap_id WHERE h.landskap_id IS NULL');
		DB::delete('DELETE pj FROM sandit_mansion_jordnatur_post pj LEFT JOIN sandit_mansion_post p ON pj.post_id=p.id WHERE p.id IS NULL');
        DB::delete('DELETE j FROM sandit_mansion_jordnatur j LEFT JOIN sandit_mansion_jordnatur_post pj ON pj.jordnatur_id=j.id WHERE pj.jordnatur_id IS NULL');
        DB::delete('DELETE p 
            FROM sandit_mansion_person p
                LEFT JOIN sandit_mansion_post pa ON p.id=pa.agare_person_id
                LEFT JOIN sandit_mansion_post pm1 ON p.id=pm1.maka1_person_id
                LEFT JOIN sandit_mansion_post pm2 ON p.id=pm2.maka2_person_id
            WHERE pa.agare_person_id IS NULL 
                AND pm1.maka1_person_id IS NULL
                AND pm2.maka2_person_id IS NULL');
    }*/

    public function deleteImportAndAllItsData(int $import_id): void
    {
		$this->deleteImport($import_id);
		Post::where('import_id', $import_id)->delete();
        DB::delete('DELETE s FROM sandit_mansion_status s LEFT JOIN sandit_mansion_post p ON s.id=p.status_id WHERE p.status_id IS NULL');
        DB::delete('DELETE k FROM sandit_mansion_kalla k LEFT JOIN sandit_mansion_post p ON k.id=p.kalla_id WHERE p.kalla_id IS NULL');
        DB::delete('DELETE i FROM sandit_mansion_import i LEFT JOIN sandit_mansion_post p ON i.id=p.import_id WHERE p.import_id IS NULL');
        DB::delete('DELETE g FROM sandit_mansion_gard g LEFT JOIN sandit_mansion_post p ON g.id=p.gard_id WHERE p.gard_id IS NULL');
        DB::delete('DELETE s FROM sandit_mansion_socken s LEFT JOIN sandit_mansion_gard g ON s.id=g.socken_id WHERE g.socken_id IS NULL');
        DB::delete('DELETE h FROM sandit_mansion_harad h LEFT JOIN sandit_mansion_socken s ON h.id=s.harad_id WHERE s.harad_id IS NULL');
        DB::delete('DELETE l FROM sandit_mansion_landskap l LEFT JOIN sandit_mansion_harad h ON l.id=h.landskap_id WHERE h.landskap_id IS NULL');
		DB::delete('DELETE pj FROM sandit_mansion_jordnatur_post pj LEFT JOIN sandit_mansion_post p ON pj.post_id=p.id WHERE p.id IS NULL');
        DB::delete('DELETE j FROM sandit_mansion_jordnatur j LEFT JOIN sandit_mansion_jordnatur_post pj ON pj.jordnatur_id=j.id WHERE pj.jordnatur_id IS NULL');
        DB::delete('DELETE p 
            FROM sandit_mansion_person p
                LEFT JOIN sandit_mansion_post pa ON p.id=pa.agare_person_id
                LEFT JOIN sandit_mansion_post pm1 ON p.id=pm1.maka1_person_id
                LEFT JOIN sandit_mansion_post pm2 ON p.id=pm2.maka2_person_id
            WHERE pa.agare_person_id IS NULL 
                AND pm1.maka1_person_id IS NULL
                AND pm2.maka2_person_id IS NULL');
    }


    public function getFileName(int $import_id) : string
    {
        $import = Import::find($import_id);
        return $import->filename;
    }


    public function getImports(string $import_type) : Import
    {
        return Import::leftjoin('users', 'import.user_id', '=', 'users.id')
                    ->select(array('import.id','import.filename','import.import_type','import.total_rows','import.saved_rows','users.username','import.created_at'))
                    ->where('import.import_type','=',$import_type);
    }


    public function getImportType(int $import_id) : string
    {
        $import = Import::find($import_id);
        return $import->import_type;
    }


    protected function savePost(array $post_data) : int
    {
        $param = array();

        if (isset($post_data['ar_borjan'])) {
            $param['ar_borjan'] = $post_data['ar_borjan'];
        }
        if (isset($post_data['ar_borjan_anm'])) {
            $param['ar_borjan_anm'] = $post_data['ar_borjan_anm'];
        }
        if (isset($post_data['ar'])) {
            $param['ar_borjan'] = $post_data['ar'];
        }
        if (isset($post_data['ar_slut'])) {
            $param['ar_slut'] = $post_data['ar_slut'];
        }
        if (isset($post_data['ar_slut_anm'])) {
            $param['ar_slut_anm'] = $post_data['ar_slut_anm'];
        }
        if (isset($post_data['status'])) {
            $param['status_id'] = $this->saveStatus($post_data['status']);
        }
        if (isset($post_data['agarr'])) {
            $param['ag_arr'] = $post_data['agarr'];
        }
        if (isset($post_data['typ'])) {
            $param['typ'] = $post_data['typ'];
        }
        if (isset($post_data['kommentar'])) {
            $param['kommentar'] = $post_data['kommentar'];
        }
        $param['gard_id'] = $this->saveGard($post_data);
        $param['kalla_id'] = $this->saveKalla($post_data['kalla']);
        $param['import_id'] = $post_data['import_id'];

        $post = Post::create($param);

        return $post->id;
    }

	protected function saveGard(array $post_data) : int
    {
		// If estate import
        if (isset($post_data['herrgard'])) {

            if (is_null($post_data['herrgard']) || $post_data['herrgard'] == 'Uppgift saknas') {
                $herrgard_id = 0;
            } else {
                $herrgard = new Gard;
                $param_herrgard = array(
					'id' => $post_data['lopnummer'],
					'toraid' => $post_data['toraid'],
					'gard' => $post_data['herrgard'],
                    'socken' => $post_data['socken_herrgard'],
                    'harad' => $post_data['harad_herrgard'],
                    'landskap' => $post_data['landskap_herrgard']
				);
                $herrgard_id = $herrgard->getGardId($param_herrgard);
            }
        }
        $gard = new Gard;
        $param_gard = array(
			'id' => $post_data['lopnummer'],
			'toraid' => $post_data['toraid'],
			'gard' => $post_data['gard'],
			'socken' => $post_data['socken'],
            'harad' => $post_data['harad'],
			'landskap' => $post_data['landskap']
		);
        if (isset($herrgard_id)) {
            $param_gard['tillhor_herrgard'] = $herrgard_id;
            $param_gard['nummer'] = $post_data['nr'];

        }
        return $gard->getGardId($param_gard);
    }


    protected function savePerson(array $post_data, string $type, int $post_id)
    {
        switch($type) {
            case 'person':
                $param = array( 'namn' => $post_data['namn'],
                                'efternamn' => $post_data['efternamn'],
                                'titel_tjanst' => $post_data['titel_tjanst'],
                                'titel_familj' => $post_data['titel_familj']);
                $post_field = 'agare_person_id';
                break;
            case 'person_estate':
                $param = array( 'namn' => $post_data['agare_fornamn'],
                                'efternamn' => $post_data['agare_efternamn'],
                                'titel_tjanst' => $post_data['agare_titel_tjanst'],
                                'titel_familj' => $post_data['agare_titel_familj']);
                $post_field = 'agare_person_id';
                break;
            case 'maka1':
                $param = array( 'namn' => $post_data['m_1_namn'],
                                'efternamn' => $post_data['m_1_efternamn'],
                                'titel_tjanst' => $post_data['m_1_titel_tjanst'],
                                'titel_familj' => $post_data['m_1_titel_familj']);
                $post_field = 'maka1_person_id';
                break;
            case 'maka2':
                $param = array( 'namn' => $post_data['m_2_namn'],
                                'efternamn' => $post_data['m_2_efternamn'],
                                'titel_tjanst' => $post_data['m_2_titel_tjanst'],
                                'titel_familj' => $post_data['m_2_titel_familj']);
                $post_field = 'maka2_person_id';
                break;
        }

        foreach ($param as $p) {

            if ( ! is_null($p)) {
                $person = Person::firstOrCreate($param);
                $post = Post::find($post_id);
                $post->$post_field = $person->id;
				$post->save();
                break;
            }
        }
    }


    protected function saveStorlek(array $post_data, string $type, int $post_id)
    {
        $post = Post::find($post_id);

        if ($type == 'herrgard') {
            $post->storlek_herrgard_mtl = $post_data['storlek_herrgard_mtl'];
            $post->storlek_har = $post_data['storlek_har'];
            $post->storlek_aker_har = $post_data['storlek_aker_har'];
            $post->gods_mantal = $post_data['gods_mtl'];
            $post->gods_hektar = $post_data['gods_har'];
            $post->gods_aker_hektar = $post_data['gods_aker_har'];
            $post->taxering = $post_data['taxering'];
        } else {
            $post->storlek_herrgard_mtl = $post_data['mtl'];
            $post->brukareforhallande = $post_data['brukareforhallande_siffrabonde'];
        }
        $post->save();
    }


	protected function saveJordnatur(array $post_data, int $post_id)
    {
        if ( ! empty($post_data['jordnatur'])) {
			$jordnaturer = $this->splitJordnaturString($post_data['jordnatur']);

			foreach ($jordnaturer as $jordnatur) {
				$natur = Jordnatur::firstOrCreate(array('namn' => $jordnatur));
				DB::table('sandit_mansion_jordnatur_post')
					->insert(['jordnatur_id' => $natur->id, 'post_id' => $post_id]);
			}

		}
    }


    protected function splitJordnaturString(string $jordnatur) : array
    {
        $jordnaturer = explode(';', $jordnatur);
        return array_map('trim',$jordnaturer);
    }


    protected function saveStatus(string $status) : int
    {
        if (empty($status)) {
            return null;
        }
        $status = Status::firstOrCreate(array('namn' => $status));
        return $status->id;
    }


    protected function saveKalla(string $kalla) : int
    {
        if( empty($kalla)) {
            return null;
        }
        
        $kalla = Kalla::firstOrCreate(array('namn' => $kalla));
        return $kalla->id;
    }


    public function isIdExisting(array $post_data) : bool
    {
        extract($post_data);
        
        $db_gard = Gard::find($lopnummer);

        if (! $db_gard || 
            (strcasecmp($db_gard->namn, $gard) == 0 && 
            strcasecmp($db_gard->socken->namn, $socken) == 0 &&
            strcasecmp($db_gard->socken->harad->namn, $harad) == 0 && 
            strcasecmp($db_gard->socken->harad->landskap->namn, $landskap) == 0)) 
                {
            return false;
        }
        return true;
    }
}
