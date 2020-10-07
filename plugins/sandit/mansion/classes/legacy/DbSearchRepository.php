<?php namespace Sandit\Mansion\Classes;


interface SearchRepositoryInterface
{
	public function searchMansion($search);
    public function searchPerson($search);
}


class DbSearchRepository implements SearchRepositoryInterface
{

	public function searchMansion($data)
	{
		/*if (empty($data['mansion']) && $data['landskap'] == 0) {
			return false;
		}*/
		$param = array();

		$query = "SELECT g.id AS 'id',
			CONCAT(UCASE(MID(g.namn ,1,1)),MID(g.namn ,2)) AS 'gard',
			CONCAT(UCASE(MID(s.namn ,1,1)),MID(s.namn ,2)) AS 'socken',
			CONCAT(UCASE(MID(h.namn ,1,1)),MID(h.namn ,2)) AS 'harad',
			CONCAT(UCASE(MID(l.namn ,1,1)),MID(l.namn ,2)) AS 'landskap'
			FROM gard g JOIN socken s ON g.socken_id=s.id
			JOIN harad h ON s.harad_id=h.id
			JOIN landskap l ON h.landskap_id=l.id
			WHERE 1 ";

		/*if ($data['type'] == 'mansion') {
			$query .= "AND tillhor_herrgard IS NULL ";
		} else {
			$query .= "AND tillhor_herrgard IS NOT NULL ";
		}*/

		$query .= "AND tillhor_herrgard IS NULL ";

		if (! empty($data['mansion'])) {
			$query .= " AND g.namn LIKE ?";

			switch ($data['accuracy']) {
				case 'anywhere':
					$param[] = '%'.$data['mansion'].'%';
					break;
				case 'first':
					$param[] =  $data['mansion'].'%';
					break;
				case 'last':
					$param[] =  '%'.$data['mansion'];
					break;
				case 'strict':
					$param[] =  $data['mansion'];
					break;
			}
		}
		if ($data['landskap'] != 0) {
			$query .= " AND l.id = ?";
			$param[] = $data['landskap'];
		}
		if ((! is_null($data['harad'])) && $data['harad'] != 0) {
			$query .= " AND h.id = ?";
			$param[] = $data['harad'];
		}
		if ((! is_null($data['socken'])) && $data['socken'] != 0) {
			$query .= " AND s.id = ?";
			$param[] = $data['socken'];
		}
		return DB::select($query, $param);
	}


	public function getLandskap()
	{
		$landskap = Landskap::orderBy('namn')->get();
		$names = array();
		$names[0] = '-- Alla --';

		foreach ($landskap as $l) {
			$names[$l->id] = ucfirst($l->namn);
		}
		//echo '<pre>'.print_r($names,true).'</pre>';exit();
		return $names;
	}


	public function getHarad($landskap_id=0)
	{
		if ($landskap_id == 0) {
			return array();
		}
		$harad = Harad::where('landskap_id', '=', $landskap_id)->orderBy('namn')->get();
		$names = array();
		$names[0] = '-- Alla --';

		foreach ($harad as $h) {
			$names[$h->id] = ucfirst($h->namn);
		}
		//echo '<pre>'.print_r($names,true).'</pre>';exit();
		return $names;
	}


	public function getSocken($landskap_id=0,$harad_id=0)
	{
		if ($landskap_id == 0 || $harad_id == 0) {
			return array();
		}
		$socken = Socken::where('harad_id', '=', $harad_id)->orderBy('namn')->get();
		$names = array();


		$names[0] = '-- Alla --';

		foreach ($socken as $s) {
			$names[$s->id] = ucfirst($s->namn);
		}
		//echo '<pre>'.print_r($names,true).'</pre>';exit();
		return $names;
	}

	public function searchPerson($search){}

		/*    public function ownerPostsSql()
		    {
		        return "SELECT g.namn AS 'gard',
		            s.namn AS 'socken',
		            h.namn AS 'harad',
		            l.namn AS 'landskap',
		            st.namn AS 'status',
		            p.tid_fran AS 'ar_borjan',
		            p.tid_till AS 'ar_slut',
		            p.tid_anm AS 'ar_anm',
		            p.natur AS 'jordnatur',
		            p.agar_arr AS 'agarr',
		            p.typ AS 'typ',
		            pe_agare.namn AS 'namn',
		            pe_agare.efternamn AS 'efternamn',
		            pe_agare.titel_tjanst AS 'titel_tjanst',
		            pe_agare.titel_familj AS 'titel_familj',
		            pe_maka1.namn AS 'm_1_namn',
		            pe_maka1.efternamn AS 'm_1_efternamn',
		            pe_maka1.titel_tjanst AS 'm_1_titel_tjanst',
		            pe_maka1.titel_familj AS 'm_1_titel_familj',
		            pe_maka2.namn AS 'm_2_namn',
		            pe_maka2.efternamn AS 'm_2_efternamn',
		            pe_maka2.titel_tjanst AS 'm_2_titel_tjanst',
		            pe_maka2.titel_familj AS 'm_2_titel_familj',
		            k.namn AS 'kalla'
		            FROM sandit_mansion_post p
		                JOIN sandit_mansion_gard g ON p.gard_id = g.id
		                JOIN sandit_mansion_socken s ON g.socken_id = s.id
		                JOIN sandit_mansion_harad h ON s.harad_id = h.id
		                JOIN sandit_mansion_landskap l ON h.landskap_id = l.id
		                LEFT JOIN sandit_mansion_status st ON p.status_id = st.id
		                LEFT JOIN sandit_mansion_person pe_agare ON pe_agare.id = p.agare_person_id
		                LEFT JOIN sandit_mansion_person pe_maka1 ON pe_maka1.id = p.maka1_person_id
		                LEFT JOIN sandit_mansion_person pe_maka2 ON pe_maka2.id = p.maka2_person_id
		                LEFT JOIN sandit_mansion_kalla k ON p.kalla_id = k.id
		            WHERE p.import_id = ?";
		    }


		    public function mansionPostsSql()
		    {
		        return "SELECT g.namn AS 'gard',
		            s.namn AS 'socken',
		            h.namn AS 'harad',
		            l.namn AS 'landskap',
		            p.natur AS 'jordnatur',
		            p.mantal AS 'storlek_herrgard_mtl',
		            p.hektar AS 'storlek_har',
		            p.aker_hektar AS 'storlek_aker_har',
		            p.gods_mantal AS 'gods_mtl',
		            p.gods_hektar AS 'gods_har',
		            p.gods_aker_hektar AS 'gods_aker_har',
		            p.taxering AS 'taxering',
		            st.namn AS 'status',
		            p.tid_fran AS 'ar_borjan',
		            p.tid_till AS 'ar_slut',
		            p.tid_anm AS 'ar_anm',
		            p.agar_arr AS 'agarr',
		            p.typ AS 'typ',
		            pe_agare.namn AS 'namn',
		            pe_agare.efternamn AS 'efternamn',
		            pe_agare.titel_tjanst AS 'titel_tjanst',
		            pe_agare.titel_familj AS 'titel_familj',
		            pe_maka1.namn AS 'm_1_namn',
		            pe_maka1.efternamn AS 'm_1_efternamn',
		            pe_maka1.titel_tjanst AS 'm_1_titel_tjanst',
		            pe_maka1.titel_familj AS 'm_1_titel_familj',
		            pe_maka2.namn AS 'm_2_namn',
		            pe_maka2.efternamn AS 'm_2_efternamn',
		            pe_maka2.titel_tjanst AS 'm_2_titel_tjanst',
		            pe_maka2.titel_familj AS 'm_2_titel_familj',
		            k.namn AS 'kalla'
		            FROM sandit_mansion_post p
		                JOIN sandit_mansion_gard g ON p.gard_id = g.id
		                JOIN sandit_mansion_socken s ON g.socken_id = s.id
		                JOIN sandit_mansion_harad h ON s.harad_id = h.id
		                JOIN sandit_mansion_landskap l ON h.landskap_id = l.id
		                LEFT JOIN sandit_mansion_status st ON p.status_id = st.id
		                LEFT JOIN sandit_mansion_person pe_agare ON pe_agare.id = p.agare_person_id
		                LEFT JOIN sandit_mansion_person pe_maka1 ON pe_maka1.id = p.maka1_person_id
		                LEFT JOIN sandit_mansion_person pe_maka2 ON pe_maka2.id = p.maka2_person_id
		                LEFT JOIN kalla k ON p.kalla_id = k.id
		            WHERE p.import_id = ?";
		    }



		    public function estatePostsSql()
		    {
		        return "SELECT g.namn AS 'gard',
		            s.namn AS 'socken',
		            h.namn AS 'harad',
		            l.namn AS 'landskap',
		            g.nummer AS 'nr',
		            p.natur AS 'jordnatur',
		            st.mantal AS 'mtl',
		            st.brukareforhallande AS 'brukareforhallande_siffrabonde',
		            p.tid_fran AS 'ar',
		            p.agar_arr AS 'agarr',
		            pe.namn AS 'agare_fornamn',
		            pe.efternamn AS 'agare_efternamn',
		            pe.titel_tjanst AS 'agare_titel_tjanst',
		            pe.titel_familj AS 'agare_titel_familj',
		            p.kommentar AS 'kommentar',
		            hg.namn AS 'herrgard',
		            hs.namn AS 'socken_herrgard',
		            hh.namn AS 'harad_herrgard',
		            hl.namn AS 'landskap_herrgard',
		            k.namn AS 'kalla'
		            FROM sandit_mansion_post p
		                JOIN sandit_mansion_gard g ON p.gard_id = g.id
		                JOIN sandit_mansion_socken s ON g.socken_id = s.id
		                JOIN sandit_mansion_harad h ON s.harad_id = h.id
		                JOIN sandit_mansion_landskap l ON h.landskap_id = l.id
		                LEFT JOIN sandit_mansion_person_post pp ON pp.post_id= p.id
		                LEFT JOIN sandit_mansion_person pe ON pe.id = pp.person_id
		                LEFT JOIN sandit_mansion_storlek st ON p.id = st.post_id AND st.typ = 'gard'
		                LEFT JOIN sandit_mansion_gard hg ON g.tillhor_herrgard = hg.id
		                LEFT JOIN sandit_mansion_socken hs ON hg.socken_id = hs.id
		                LEFT JOIN sandit_mansion_harad hh ON hs.harad_id = hh.id
		                LEFT JOIN sandit_mansion_landskap hl ON hh.landskap_id = hl.id
		                LEFT JOIN sandit_mansion_kalla k ON p.kalla_id = k.id
		            WHERE p.import_id = ?";
		    }*/

}
