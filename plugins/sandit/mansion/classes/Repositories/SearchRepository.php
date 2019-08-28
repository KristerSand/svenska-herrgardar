<?php namespace Sandit\Mansion\Classes\Repositories;

use DB;
use Input;
use Sandit\Mansion\Classes\Repositories\SearchRepositoryInterface;
use Sandit\Mansion\Models\Import;
use Sandit\Mansion\Models\Status;
use Sandit\Mansion\Models\Gard;
use Sandit\Mansion\Models\Kalla;
use Sandit\Mansion\Models\Post;
use Sandit\Mansion\Models\Person;
use Sandit\Mansion\Models\Jordnatur;

class SearchRepository implements SearchRepositoryInterface
{
	const DB_PREFIX = 'sandit_mansion_';

	public $trunc_list = array(
		'anywhere'=>'var som helst',
		'first' => 'början',
		'last'=>'slutet',
		'strict'=>'exakt matchning'
	);
    public $year_list = array('', '1740', '1780', '1820', '1860', '1900', '1940');
    public $storlek_operator_list = array(
		'eq' => '=',
		'lt' => '<',
		'lteq' => '<=',
		'gt' => '>',
		'gteq' => '>='
	);

	public function search($data)
	{
		//dd($data);
		if (empty($data)) {
			return [];
		}
		array_walk($data, 'trim');

		$param = array();

		$query = "SELECT DISTINCT g.id AS 'id',
				g.namn AS 'gard',
				s.namn AS 'socken',
				h.namn AS 'harad',
				l.namn AS 'landskap',
				(SELECT status.namn 
				FROM sandit_mansion_post post 
					JOIN sandit_mansion_gard gard ON post.gard_id=gard.id 
					LEFT JOIN sandit_mansion_status status ON post.status_id=status.id 
				WHERE gard.id=g.id 
				GROUP BY status.namn,gard.id HAVING COUNT(*) 
				ORDER BY count(*) DESC LIMIT 1) AS 'status'
			FROM sandit_mansion_post p
				JOIN sandit_mansion_import i ON p.import_id=i.id
				JOIN sandit_mansion_gard g ON p.gard_id=g.id
				JOIN sandit_mansion_socken s ON g.socken_id=s.id
				JOIN sandit_mansion_harad h ON s.harad_id=h.id
				JOIN sandit_mansion_landskap l ON h.landskap_id=l.id
				LEFT JOIN sandit_mansion_status st ON p.status_id=st.id
	            LEFT JOIN sandit_mansion_person pe ON p.agare_person_id = pe.id
	            LEFT JOIN sandit_mansion_person pe_m1 ON p.maka1_person_id = pe_m1.id
	            LEFT JOIN sandit_mansion_person pe_m2 ON p.maka2_person_id = pe_m2.id
				LEFT JOIN sandit_mansion_jordnatur_post jp ON p.id = jp.post_id
				LEFT JOIN sandit_mansion_jordnatur j ON jp.jordnatur_id = j.id

			WHERE 1 ";

		if (isset($data['gard']) && ! empty($data['gard'])) {
			$query .= " AND g.namn LIKE ?";
			$param[] = "%".$data['gard']."%";
		}
		if (isset($data['harad']) && $data['landskap'] != '0') {
			$query .= " AND l.id = ?";
			$param[] = $data['landskap'];
		}
		if (isset($data['harad']) && $data['harad'] != '0') {
			$query .= " AND h.id = ?";
			$param[] = $data['harad'];
		}
		if (isset($data['socken']) && $data['socken'] != '0') {
			$query .= " AND s.id = ?";
			$param[] = $data['socken'];
		}
		if (isset($data['person_namn']) && ! empty($data['person_namn'])) {
			$query .= " AND pe.namn LIKE ?";
			$param[] = "%".$data['person_namn']."%";
		}
		if (isset($data['person_efternamn']) && ! empty($data['person_efternamn'])) {
			$query .= " AND pe.efternamn LIKE ?";
			$param[] = "%".$data['person_efternamn']."%";
		}
		if (isset($data['person_titel_tjanst']) &&  ! empty($data['person_titel_tjanst'])) {
			$query .= " AND pe.titel_tjanst LIKE ?";
			$param[] = '%'.$data['person_titel_tjanst'].'%';
		}
		if (isset($data['person_titel_familj']) && $data['person_titel_familj'] != '0') {
			$query .= " AND pe.titel_familj = ?";
			$param[] = $data['person_titel_familj'];
		}
		if (isset($data['maka_namn']) && ! empty($data['maka_namn'])) {
			$query .= " AND (pe_m1.namn LIKE ? OR pe_m2.namn LIKE ?)";
			$param[] = "%".$data['maka_namn']."%";
			$param[] = "%".$data['maka_namn']."%";
		}
		if (isset($data['maka_efternamn']) && ! empty($data['maka_efternamn'])) {
			$query .= " AND (pe_m1.efternamn LIKE ? OR pe_m2.efternamn LIKE ?)";
			$param[] = "%".$data['maka_efternamn']."%";
			$param[] = "%".$data['maka_efternamn']."%";
		}
		if (isset($data['maka_titel_tjanst']) && ! empty($data['maka_titel_tjanst'])) {
			$query .= " AND (pe_m1.titel_tjanst LIKE ? OR pe_m2.titel_tjanst LIKE ?)";
			$param[] = '%'.$data['maka_titel_tjanst'].'%';
			$param[] = '%'.$data['maka_titel_tjanst'].'%';
		}
		if (isset($data['maka_titel_familj']) && $data['maka_titel_familj'] != '0') {
			$query .= " AND (pe_m1.titel_familj = ? OR pe_m2.titel_familj LIKE ?)";
			$param[] = $data['maka_titel_familj'];
			$param[] = $data['maka_titel_familj'];
		}
		if (isset($data['tid_fran']) && is_numeric($data['tid_fran'])) {
			$query .= " AND IF(p.tid_fran IS NOT NULL AND p.tid_till IS NOT NULL, p.tid_fran <= ? AND p.tid_till >= ?,
							IF(p.tid_fran IS NOT NULL, p.tid_fran = ?, IF(p.tid_till IS NOT NULL, p.tid_till = ?, 0)))";
			$param[] = $data['tid_fran'];
			$param[] = $data['tid_fran'];
			$param[] = $data['tid_fran'];
			$param[] = $data['tid_fran'];
		}
		if (isset($data['status']) && $data['status'] != '0') {
			$query .= " AND st.id = ?";
			$param[] = $data['status'];
		}
		if (isset($data['jordnatur']) && ! empty($data['jordnatur'])) {
			$query .= " AND j.namn like ?";
			$param[] = '%'.$data['jordnatur'].'%';
		}
		if (isset($data['agar_arr']) && $data['agar_arr'] != '0') {
			$query .= " AND p.ag_arr = ?";
			$param[] = $data['agar_arr'];
		}
		if (isset($data['typ']) && ! empty($data['typ'])) {
			$query .= " AND p.typ LIKE ?";
			$param[] = '%'. $data['typ'].'%';
		}
		if (isset($data['herrgard_mantal']) 
			&& (! empty($data['herrgard_mantal'])
			|| $data['herrgard_mantal'] == '0')) {
			$data['herrgard_mantal'] = str_replace(',', '.', $data['herrgard_mantal']);
			
			switch($data['herrgard_mantal_operator']) {
				case '=':
					$query .= " AND p.storlek_herrgard_mtl = ?";
					break;
				case '<':
					$query .= " AND p.storlek_herrgard_mtl < ?";
					break;
				case '>':
					$query .= " AND p.storlek_herrgard_mtl > ?";
					break;
			}
			$query .= " AND p.storlek_herrgard_mtl IS NOT NULL";
			$param[] = $data['herrgard_mantal'];
		}
		if (isset($data['herrgard_hektar']) 
			&& (! empty($data['herrgard_hektar'])
			|| $data['herrgard_hektar'] == '0')) {
			switch($data['herrgard_hektar_operator']) {
				case '=':
					$query .= " AND p.storlek_har = ?";
					break;
				case '<':
					$query .= " AND p.storlek_har < ?";
					break;
				case '>':
					$query .= " AND p.storlek_har > ?";
					break;
			}
			$query .= " AND p.storlek_har IS NOT NULL";
			$param[] = $data['herrgard_hektar'];
		}
		if (isset($data['herrgard_aker_hektar']) 
			&& (! empty($data['herrgard_aker_hektar'])
			|| $data['herrgard_aker_hektar'] == '0')) {
			switch($data['herrgard_aker_hektar_operator']) {
				case '=':
					$query .= " AND p.storlek_aker_har = ?";
					break;
				case '<':
					$query .= " AND p.storlek_aker_har < ?";
					break;
				case '>':
					$query .= " AND p.storlek_aker_har > ?";
					break;
			}
			$query .= " AND p.storlek_aker_har IS NOT NULL";
			$param[] = $data['herrgard_aker_hektar'];
		}
		if (isset($data['gods_mantal']) 
			&& (! empty($data['gods_mantal'])
			|| $data['gods_mantal'] == '0')) {
			$data['gods_mantal'] = str_replace(',', '.', $data['gods_mantal']);

			switch($data['gods_mantal_operator']) {
				case '=':
					$query .= " AND p.gods_mantal = ?";
					break;
				case '<':
					$query .= " AND p.gods_mantal < ?";
					break;
				case '>':
					$query .= " AND p.gods_mantal > ?";
					break;
			}
			$query .= " AND p.gods_mantal IS NOT NULL";
			$param[] = $data['gods_mantal'];
		}
		if (isset($data['gods_hektar']) 
			&& (! empty($data['gods_hektar'])
			|| $data['gods_hektar'] == '0')) {
			switch($data['gods_hektar_operator']) {
				case '=':
					$query .= " AND p.gods_hektar = ?";
					break;
				case '<':
					$query .= " AND p.gods_hektar < ?";
					break;
				case '>':
					$query .= " AND p.gods_hektar > ?";
					break;
			}
			$query .= " AND p.gods_hektar IS NOT NULL";
			$param[] = $data['gods_hektar'];
		}
		if (isset($data['gods_aker_hektar']) 
			&& (! empty($data['gods_aker_hektar'])
			|| $data['gods_aker_hektar'] == '0')) {
			switch($data['gods_aker_hektar_operator']) {
				case '=':
					$query .= " AND p.gods_aker_hektar = ?";
					break;
				case '<':
					$query .= " AND p.gods_aker_hektar < ?";
					break;
				case '>':
					$query .= " AND p.gods_aker_hektar > ?";
					break;
			}
			$query .= " AND p.gods_aker_hektar IS NOT NULL";
			$param[] = $data['gods_aker_hektar'];
		}
		if (isset($data['gods_taxering']) 
			&& (! empty($data['gods_taxering'])
			|| $data['gods_taxering'] == '0')) {
			switch($data['gods_taxering_operator']) {
				case '=':
					$query .= " AND p.taxering = ?";
					break;
				case '<':
					$query .= " AND p.taxering < ?";
					break;
				case '>':
					$query .= " AND p.taxering > ?";
					break;
			}
			$query .= " AND p.taxering IS NOT NULL";
			$param[] = $data['gods_taxering'];
		}


//dd($query,$param);
		$query = "SELECT 
					tmp.id AS 'id',
					CONCAT(UCASE(MID(tmp.gard ,1,1)),MID(tmp.gard ,2)) AS 'gard',
					CONCAT(UCASE(MID(tmp.socken ,1,1)),MID(tmp.socken ,2)) AS 'socken',
					CONCAT(UCASE(MID(tmp.harad ,1,1)),MID(tmp.harad ,2)) AS 'harad',
					CONCAT(UCASE(MID(tmp.landskap ,1,1)),MID(tmp.landskap ,2)) AS 'landskap',
					CONCAT(UCASE(MID(tmp.status ,1,1)),MID(tmp.status ,2)) AS 'status',
					COUNT(*) AS antal 
					FROM ($query) AS tmp JOIN sandit_mansion_post AS post
					WHERE tmp.id = post.gard_id
					GROUP BY tmp.id,tmp.gard,tmp.socken,tmp.harad,tmp.landskap,tmp.status 
					ORDER BY tmp.gard";
					//dd($query,$param,$data);
		return DB::select($query, $param);
	}


	public function getGardar($ids, $id_type, $relations)
    {
		if (empty($ids)) {
			$gardar = Gard::with('socken.harad.landskap')->get();
		} elseif ($id_type == 'id') {
            $gardar = Gard::with('socken.harad.landskap')->whereIn('id', $ids)->get();
        } elseif ($id_type == 'toraid') {
            $gardar = Gard::with('socken.harad.landskap')->whereIn('toraid', $toraids)->get();
        } else {
			return collect();
		}
        if ( ! empty($relations)) {

            if (in_array('post', $relations)) {

                foreach ($gardar as &$gard) {
                    $gard->poster = Post::with('jordnatur','agare','maka1','maka2','status','kalla')
                        ->where('gard_id','=',$gard->id)
                        ->get();
                }
            }
        }
        return $gardar;
    }



	public function searchSize($data)
	{
		$param = array();

		$query = "SELECT DISTINCT g.id AS 'id',
				CONCAT(UCASE(MID(g.namn ,1,1)),MID(g.namn ,2)) AS 'gard',
				CONCAT(UCASE(MID(s.namn ,1,1)),MID(s.namn ,2)) AS 'socken',
				CONCAT(UCASE(MID(h.namn ,1,1)),MID(h.namn ,2)) AS 'harad',
				CONCAT(UCASE(MID(l.namn ,1,1)),MID(l.namn ,2)) AS 'landskap',
				GROUP_CONCAT(st.namn) AS 'typ'
			FROM post p
				JOIN import i on p.import_id=i.id
				JOIN gard g ON p.gard_id=g.id
				JOIN socken s ON g.socken_id=s.id
				JOIN harad h ON s.harad_id=h.id
				JOIN landskap l ON h.landskap_id=l.id
				LEFT JOIN status st ON p.status_id=st.id
				LEFT JOIN person_post ppe ON ppe.post_id = p.id AND ppe.typ = 'person'
	            LEFT JOIN person pe ON pe.id = ppe.person_id
	            LEFT JOIN person_post ppm1 ON ppm1.post_id = p.id AND ppm1.typ = 'maka1'
	            LEFT JOIN person pe_m1 ON pe_m1.id = ppm1.person_id
	            LEFT JOIN person_post ppm2 ON ppm2.post_id = p.id AND ppm2.typ = 'maka2'
	            LEFT JOIN person pe_m2 ON pe_m2.id = ppm2.person_id
	            LEFT JOIN storlek sto ON sto.post_id = p.id
			WHERE 1 ";

		$query .= "AND import_type = 'mansion' ";
		$query .= "AND tillhor_herrgard IS NULL ";
		$query .= "AND (sto.mantal IS NOT NULL OR sto.hektar IS NOT NULL OR sto.aker_hektar IS NOT NULL OR sto.taxering IS NOT NULL)";


		if ($data['landskap'] != 0) {
			$query .= " AND l.id = ?";
			$param[] = $data['landskap'];
		}
		if (( ! is_null($data['harad'])) && $data['harad'] != 0) {
			$query .= " AND h.id = ?";
			$param[] = $data['harad'];
		}
		if (( ! is_null($data['socken'])) && $data['socken'] != 0) {
			$query .= " AND s.id = ?";
			$param[] = $data['socken'];
		}
		if ( ! empty($data['mansion'])) {
			$query .= " AND g.namn LIKE ?";
			$param[] = '%'.$data['mansion'].'%';
		}
		if (( ! is_null($data['status'])) && $data['status'] != 0) {
			$query .= " AND st.id = ?";
			$param[] = $data['status'];
		}
		/*if ( ! empty($data['person'])) {
			$query .= " AND (pe.namn LIKE ?
						OR pe.efternamn LIKE ?
						OR pe_m1.namn LIKE ?
						OR pe_m1.efternamn LIKE ?
						OR pe_m2.namn LIKE ?
						OR pe_m2.efternamn LIKE ?)";
			$param[] = '%'.$data['person'].'%';
			$param[] = '%'.$data['person'].'%';
			$param[] = '%'.$data['person'].'%';
			$param[] = '%'.$data['person'].'%';
			$param[] = '%'.$data['person'].'%';
			$param[] = '%'.$data['person'].'%';
		}*/
		if ( ! empty($data['person'])) {
			$query .= " AND (pe.namn LIKE ?
						OR pe.efternamn LIKE ?)";
			$param[] = '%'.$data['person'].'%';
			$param[] = '%'.$data['person'].'%';
		}
		if ($data['tid_fran'] != 0) {
			$tid_fran = $this->year_list[$data['tid_fran']];
			$query .= " AND IF((p.tid_fran IS NOT NULL AND p.tid_till IS NOT NULL AND p.tid_fran=p.tid_till)
				OR (p.tid_fran IS NOT NULL AND p.tid_till IS NULL), p.tid_fran BETWEEN ?-7 AND ?+7, 0)";
			$param[] = $tid_fran;
			$param[] = $tid_fran;
		}
		if (! empty($data['mansion_mantal']) && is_numeric($data['mansion_mantal'])) {
			$query .= " AND sto.typ = 'herrgard'";
			$query .= " AND sto.mantal ";
			$query .= $this->storlek_operator_list[$data['mansion_mantal_op']];
			$query .= " ?";
			$param[] = $data['mantal'];
		}
		if (! empty($data['mansion_hektar']) && is_numeric($data['mansion_hektar'])) {
			$query .= " AND sto.typ = 'herrgard'";
			$query .= " AND sto.hektar ";
			$query .= $this->storlek_operator_list[$data['mansion_hektar_op']];
			$query .= " ?";
			$param[] = $data['mansion_hektar'];
		}
		if (! empty($data['mansion_aker_hektar']) && is_numeric($data['mansion_aker_hektar'])) {
			$query .= " AND sto.typ = 'herrgard'";
			$query .= " AND sto.aker_hektar ";
			$query .= $this->storlek_operator_list[$data['mansion_aker_hektar_op']];
			$query .= " ?";
			$param[] = $data['mansion_aker_hektar'];
		}
		if (! empty($data['estate_mantal']) && is_numeric($data['estate_mantal'])) {
			$query .= " AND sto.typ = 'gods'";
			$query .= " AND sto.mantal ";
			$query .= $this->storlek_operator_list[$data['estate_mantal_op']];
			$query .= " ?";
			$param[] = $data['estate_mantal'];
		}
		if (! empty($data['estate_hektar']) && is_numeric($data['estate_hektar'])) {
			$query .= " AND sto.typ = 'gods'";
			$query .= " AND sto.hektar ";
			$query .= $this->storlek_operator_list[$data['estate_hektar_op']];
			$query .= " ?";
			$param[] = $data['estate_hektar'];
		}
		if (! empty($data['estate_aker_hektar']) && is_numeric($data['estate_aker_hektar'])) {
			$query .= " AND sto.typ = 'gods'";
			$query .= " AND sto.aker_hektar ";
			$query .= $this->storlek_operator_list[$data['estate_aker_hektar_op']];
			$query .= " ?";
			$param[] = $data['estate_aker_hektar'];
		}
		if (! empty($data['estate_taxering']) && is_numeric($data['estate_taxering'])) {
			$query .= " AND sto.typ = 'gods'";
			$query .= " AND sto.taxering ";
			$query .= $this->storlek_operator_list[$data['estate_taxering_op']];
			$query .= " ?";
			$param[] = $data['estate_taxering'];
		}
		$query .= " GROUP BY id ";
		//print_r($query);exit();
		return DB::select($query, $param);
	}



	public function searchEstate($data)
	{
		$param = array();

		$query = "SELECT DISTINCT g.id AS 'id',
				g.namn AS 'gard',
				g.nummer AS 'nummer',
				s.namn AS 'socken',
				h.namn AS 'harad',
				l.namn AS 'landskap',
				hg.id AS 'herrgard_id',
				hg.namn AS 'herrgard'
			FROM post p
				JOIN import i on p.import_id=i.id
				LEFT JOIN gard g ON p.gard_id=g.id
				LEFT JOIN socken s ON g.socken_id=s.id
				LEFT JOIN harad h ON s.harad_id=h.id
				LEFT JOIN landskap l ON h.landskap_id=l.id
				LEFT JOIN status st ON p.status_id=st.id
				LEFT JOIN person_post ppe ON ppe.post_id = p.id AND ppe.typ = 'person'
	            LEFT JOIN person pe ON pe.id = ppe.person_id
	            LEFT JOIN person_post ppm1 ON ppm1.post_id = p.id AND ppm1.typ = 'maka1'
	            LEFT JOIN person pe_m1 ON pe_m1.id = ppm1.person_id
	            LEFT JOIN person_post ppm2 ON ppm2.post_id = p.id AND ppm2.typ = 'maka2'
	            LEFT JOIN person pe_m2 ON pe_m2.id = ppm2.person_id
	            LEFT JOIN storlek sto ON sto.post_id = p.id
	           	LEFT JOIN gard hg ON g.tillhor_herrgard=hg.id
			WHERE 1 ";

		$query .= "AND i.import_type = 'estate' ";
		$query .= "AND g.tillhor_herrgard IS NOT NULL ";

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
		if (( ! is_null($data['status'])) && $data['status'] != 0) {
			$query .= " AND st.id = ?";
			$param[] = $data['status'];
		}
		if (! empty($data['person'])) {
			$query .= " AND (pe.namn LIKE ?
						OR pe.efternamn LIKE ?
						OR pe_m1.namn LIKE ?
						OR pe_m1.efternamn LIKE ?
						OR pe_m2.namn LIKE ?
						OR pe_m2.efternamn LIKE ?)";
			$param[] = '%'.$data['person'].'%';
			$param[] = '%'.$data['person'].'%';
			$param[] = '%'.$data['person'].'%';
			$param[] = '%'.$data['person'].'%';
			$param[] = '%'.$data['person'].'%';
			$param[] = '%'.$data['person'].'%';
		}
		if ($data['tid_fran'] != 0) {
			$tid_fran = $this->year_list[$data['tid_fran']];
			$query .= " AND IF((p.tid_fran IS NOT NULL AND p.tid_till IS NOT NULL AND p.tid_fran=p.tid_till)
				OR (p.tid_fran IS NOT NULL AND p.tid_till IS NULL), p.tid_fran BETWEEN ?-7 AND ?+7, 0)";
			$param[] = $tid_fran;
			$param[] = $tid_fran;
		}
		//print_r($data);exit();
		if ( ! empty($data['mantal']) && is_numeric($data['mantal'])) {
			$query .= " AND sto.typ = 'gard'";
			$query .= " AND sto.mantal ";
			$query .= $this->storlek_operator_list[$data['mantal_op']];
			$query .= " ?";
			$param[] = $data['mantal'];
		}
		if ( ! empty($data['brukare']) && is_numeric($data['brukare'])) {
			$query .= " AND sto.typ = 'gard'";
			$query .= " AND sto.brukareforhallande ";
			$query .= $this->storlek_operator_list[$data['brukare_op']];
			$query .= " ?";
			$param[] = $data['brukare'];
		}
		//print_r($param);
		//echo $query;exit();
		return DB::select($query, $param);
	}



	public function getLandskap($search_type = '')
	{
		if ( ! empty($search_type)) {

			$query = "SELECT DISTINCT l.id, l.namn
				FROM  post p
					JOIN import i on p.import_id=i.id
					JOIN gard g ON p.gard_id=g.id
					JOIN socken s ON g.socken_id=s.id
					JOIN harad h ON s.harad_id=h.id
					JOIN landskap l ON h.landskap_id=l.id";

			if ($search_type == 'size') {
				$query .= " JOIN storlek sto ON sto.post_id = p.id";
			}
			$query .= " WHERE i.import_type = ?";

			if ($search_type == 'size') {
				$query .= " AND (sto.mantal IS NOT NULL OR sto.hektar IS NOT NULL OR sto.aker_hektar IS NOT NULL OR sto.taxering IS NOT NULL)";
			}
			$query .= " ORDER BY l.namn";

			if (in_array($search_type,['owner','size'])) {
				$import_type = 'mansion';
			} else {
				$import_type = 'estate';
			}
			$landskap = DB::select($query, array($import_type));
		} else {
			$landskap = Landskap::all()->orderBy('name')->get();
		}

		$names = array();

		if (! $landskap) {
			return $names;
		}
		$names[0] = '';

		foreach ($landskap as $l) {

			if (! empty($l->namn)) {
				$names[$l->id] = ucfirst($l->namn);
			}
		}
		return $names;
	}


	public function getHarad($landskap_id = 0, $search_type = '')
	{
		if ($landskap_id == 0) {
			return array();
		}
		if ( ! empty($search_type)) {

			$query = "SELECT DISTINCT h.id, h.namn
				FROM  post p
					JOIN import i on p.import_id=i.id
					JOIN gard g ON p.gard_id=g.id
					JOIN socken s ON g.socken_id=s.id
					JOIN harad h ON s.harad_id=h.id
					JOIN landskap l ON h.landskap_id=l.id";

			if ($search_type == 'size') {
				$query .= " JOIN storlek sto ON sto.post_id = p.id";
			}
			$query .= " WHERE i.import_type = ? AND l.id = ?";

			if ($search_type == 'size') {
				$query .= " AND (sto.mantal IS NOT NULL OR sto.hektar IS NOT NULL OR sto.aker_hektar IS NOT NULL OR sto.taxering IS NOT NULL)";
			}
			$query .= " ORDER BY h.namn";

			if (in_array($search_type,['owner','size'])) {
				$import_type = 'mansion';
			} else {
				$import_type = 'estate';
			}
			$harader = DB::select($query, array($import_type, $landskap_id));
		} else {
			$harader = Landskap::find($landskap_id)->harader()->orderBy('namn')->get();
		}
		$names = array();

		if (! $harader) {
			return $names;
		}
		$names[0] = '';

		foreach ($harader as $harad) {

			if (! empty($harad->namn)) {
				$names[$harad->id] = ucfirst($harad->namn);
			}
		}
		return $names;
	}


	public function getSocken($landskap_id = 0, $harad_id = 0, $search_type = '')
	{
		if ($landskap_id == 0) {
			return array();
		}
		if ( ! empty($search_type)) {

			if ($harad_id == 0) {
				$query = "SELECT DISTINCT s.id, s.namn
					FROM  post p
						JOIN import i on p.import_id=i.id
						JOIN gard g ON p.gard_id=g.id
						JOIN socken s ON g.socken_id=s.id
						JOIN harad h ON s.harad_id=h.id
						JOIN landskap l ON h.landskap_id=l.id";

				if ($search_type == 'size') {
					$query .= " LEFT JOIN storlek sto ON sto.post_id = p.id";
				}
				$query .= " WHERE i.import_type = ? AND l.id = ?";

				if ($search_type == 'size') {
					$query .= " AND (sto.mantal IS NOT NULL OR sto.hektar IS NOT NULL OR sto.aker_hektar IS NOT NULL OR sto.taxering IS NOT NULL)";
				}
				$query .= " ORDER BY s.namn";

				if (in_array($search_type,['owner','size'])) {
					$import_type = 'mansion';
				} else {
					$import_type = 'estate';
				}
				$socknar = DB::select($query, array($import_type, $landskap_id));
			} else {
				$query = "SELECT DISTINCT s.id, s.namn
					FROM  post p
						JOIN import i on p.import_id=i.id
						JOIN gard g ON p.gard_id=g.id
						JOIN socken s ON g.socken_id=s.id
						JOIN harad h ON s.harad_id=h.id";

				if ($search_type == 'size') {
					$query .= " JOIN storlek sto ON sto.post_id = p.id";
				}
				$query .= " WHERE i.import_type = ? AND h.id = ?";

				if ($search_type == 'size') {
					$query .= " AND (sto.mantal IS NOT NULL OR sto.hektar IS NOT NULL OR sto.aker_hektar IS NOT NULL OR sto.taxering IS NOT NULL)";
				}
				$query .= " ORDER BY s.namn";

				if (in_array($search_type,['owner','size'])) {
					$import_type = 'mansion';
				} else {
					$import_type = 'estate';
				}
				$socknar = DB::select($query, array($import_type, $harad_id));
			}
		} else {

			if ($harad_id == 0) {
				$socknar = Landskap::find($landskap_id)->socknar()->orderBy('namn')->get();
			} else {
				$socknar = Harad::find($harad_id)->socknar()->orderBy('namn')->get();
			}
		}
		$names = array();

		if (! $socknar) {
			return $names;
		}
		$names[0] = '';

		foreach ($socknar as $socken) {

			if (! empty($socken->namn)) {
				$names[$socken->id] = ucfirst($socken->namn);
			}
		}
		return $names;
	}


	public function getMansionData($id)
	{
		$gard = Gard::find($id);
		$socken = Socken::find($gard->socken_id);
		$harad = Harad::find($socken->harad_id);
		$landskap = Landskap::find($harad->landskap_id);

		return array('gard' => $gard->namn,
			'nummer' => $gard->nummer,
			'socken' => $socken->namn,
			'harad' => $harad->namn,
			'landskap' => $landskap->namn);
	}


	public function getStatusList($search_type)
	{
		$query = "SELECT s.id,s.namn
				FROM post p
				JOIN import i on p.import_id=i.id
				JOIN status s ON p.status_id=s.id";

		if ($search_type == 'size') {
			$query .= " JOIN storlek sto ON sto.post_id = p.id";
		}
		$query .= " WHERE i.import_type = ?";

		if ($search_type == 'size') {
			$query .= " AND (sto.mantal IS NOT NULL OR sto.hektar IS NOT NULL OR sto.aker_hektar IS NOT NULL OR sto.taxering IS NOT NULL)";
		}
		$query .= " ORDER BY s.namn";

		if (in_array($search_type,['owner','size'])) {
			$import_type = 'mansion';
		} else {
			$import_type = 'estate';
		}
		$statuses = DB::select($query, array($import_type));
        $status_list[0] = '';

        foreach ($statuses as $status) {
        	$status_list[$status->id] = Helper::mb_ucfirst($status->namn);
        }
        return $status_list;
	}


	public function getTitleList($search_type, $title_type)
	{
		$query = "SELECT DISTINCT pe.";
		$query .= $title_type == 'tjänst' ? "titel_tjanst" : "titel_familj";
		$query .= " AS titel
				FROM post p
				JOIN import i ON p.import_id=i.id
				JOIN person_post pp ON pp.post_id=p.id
				JOIN person pe ON pe.id=pp.person_id";
		//$sql = "SELECT DISTINCT titel_tjanst FROM person";

		if ($search_type == 'size') {
			$query .= " JOIN storlek sto ON sto.post_id = p.id";
		}
		$query .= " WHERE i.import_type = ?";

		if ($search_type == 'size') {
			$query .= " AND (sto.mantal IS NOT NULL OR sto.hektar IS NOT NULL OR sto.aker_hektar IS NOT NULL OR sto.taxering IS NOT NULL)";
		}
		$query .= " ORDER BY titel";

		if (in_array($search_type,['owner','size'])) {
			$import_type = 'mansion';
		} else {
			$import_type = 'estate';
		}
		$rows = DB::select($query, array($import_type));
        //$titles = DB::select($sql);
        $title_list[0] = '';

        foreach ($rows as $row) {
        	$title_list[$row->titel] = Helper::mb_ucfirst($row->titel);
        }
        return $title_list;
	}


	public function getOwnerRows($gard_id, $post_id = null)
	{
        $sql = "SELECT p.id AS 'post_id',
        		g.id AS 'gard_id',
        		g.namn AS 'gard',
        		s.namn AS 'socken',
        		h.namn AS 'harad',
        		l.namn AS 'landskap',
	            st.namn AS 'status',
	            p.tid_fran AS 'ar_borjan',
	            p.tid_till AS 'ar_slut',
	            p.tid_anm AS 'ar_anm',
	            p.agar_arr AS 'agar_arr',
	            p.typ AS 'typ',
	            pe.titel_tjanst AS 'titel_tjanst',
	            pe.titel_familj AS 'titel_familj',
	            pe.namn AS 'namn',
	            pe.efternamn AS 'efternamn',
	            pe_m1.titel_tjanst AS 'maka1_titel_tjanst',
	            pe_m1.titel_familj AS 'maka1_titel_familj',
	            pe_m1.namn AS 'maka1_namn',
	            pe_m1.efternamn AS 'maka1_efternamn',
	            pe_m2.titel_tjanst AS 'maka2_titel_tjanst',
	            pe_m2.titel_familj AS 'maka2_titel_familj',
	            pe_m2.namn AS 'maka2_namn',
	            pe_m2.efternamn AS 'maka2_efternamn',
	            k.namn AS 'kalla'
            FROM post p
            	JOIN gard g ON p.gard_id = g.id
            	JOIN socken s ON g.socken_id = s.id
            	JOIN harad h ON s.harad_id = h.id
            	JOIN landskap l ON h.landskap_id = l.id
                LEFT JOIN status st ON p.status_id = st.id
                LEFT JOIN person_post ppe ON ppe.post_id = p.id AND ppe.typ = 'person'
                LEFT JOIN person pe ON pe.id = ppe.person_id
                LEFT JOIN person_post ppm1 ON ppm1.post_id = p.id AND ppm1.typ = 'maka1'
                LEFT JOIN person pe_m1 ON pe_m1.id = ppm1.person_id
                LEFT JOIN person_post ppm2 ON ppm2.post_id = p.id AND ppm2.typ = 'maka2'
                LEFT JOIN person pe_m2 ON pe_m2.id = ppm2.person_id
                LEFT JOIN kalla k ON p.kalla_id = k.id
            WHERE ";

            if ( ! is_null($gard_id)) {
            	$sql .= "p.gard_id = ? ";
            	$param = array($gard_id);
            }
            if ( ! is_null($post_id)) {
            	$sql .= "p.id = ? ";
            	$param = array($post_id);
            }
            $sql .= "ORDER BY p.tid_fran";

        $rows = DB::select($sql, $param);
		//print_r($rows);
		return $rows;
	}


	public function getOwnerRow($post_id)
	{
		$row = $this->getOwnerRows(null, $post_id);
		return array_shift($row);
	}


	public function saveOwnerRow($input)
	{
		$post = Post::find($input['post_id']);

		$ar_borjan = empty($input['ar_borjan']) ? null : $input['ar_borjan'];

		if ($ar_borjan != $post->tid_fran) {
			$post->tid_fran = $ar_borjan;
		}
		$ar_slut = empty($input['ar_slut']) ? null : $input['ar_slut'];

		if ($ar_slut != $post->tid_till) {
			$post->tid_till = $ar_slut;
		}
		$ar_anm = empty($input['ar_anm']) ? null : $input['ar_anm'];

		if ($ar_anm != $post->tid_anm) {
			$post->tid_anm = $ar_anm;
		}
		$agar_arr = empty($input['agar_arr']) ? null : $input['agar_arr'];

		if ($agar_arr != $post->agar_arr) {
			$post->agar_arr = $agar_arr;
		}
		$typ = empty($input['typ']) ? null : $input['typ'];

		if ($typ != $post->typ) {
			$post->typ = $typ;
		}
		$gard = new Gard;
    	$gard_id = $gard->alterGard($input, $post->gard_id);

    	if ($gard_id != $post->gard_id) {
    		$post->gard_id = $gard_id;
    	}
    	$status = new Status;
    	$status_id = $status->alterStatus($input, $post->status_id);

    	if ($status_id != $post->status_id) {
    		$post->status_id = $status_id;
    	}
    	$kalla = new Kalla;
    	$kalla_id = $kalla->alterKalla($input, $post->kalla_id);

    	if ($kalla_id != $post->kalla_id) {
    		$post->kalla_id = $kalla_id;
    	}

		$person = new Person;
		$person->titel_tjanst = empty($input['titel_tjanst']) ? null : $input['titel_tjanst'];
		$person->titel_familj = empty($input['titel_familj']) ? null : $input['titel_familj'];
		$person->namn = empty($input['namn']) ? null : $input['namn'];
		$person->efternamn = empty($input['efternamn']) ? null : $input['efternamn'];
    	$person->alterPerson($post->id, 'person');

    	$maka1 = new Person;
    	$maka1->titel_tjanst = empty($input['maka1_titel_tjanst']) ? null : $input['maka1_titel_tjanst'];
		$maka1->titel_familj = empty($input['maka1_titel_familj']) ? null : $input['maka1_titel_familj'];
		$maka1->namn = empty($input['maka1_namn']) ? null : $input['maka1_namn'];
		$maka1->efternamn = empty($input['maka1_efternamn']) ? null : $input['maka1_efternamn'];
    	$maka1->alterPerson($post->id, 'maka1');

    	$maka2 = new Person;
    	$maka2->titel_tjanst = empty($input['maka2_titel_tjanst']) ? null : $input['maka2_titel_tjanst'];
		$maka2->titel_familj = empty($input['maka2_titel_familj']) ? null : $input['maka2_titel_familj'];
		$maka2->namn = empty($input['maka2_namn']) ? null : $input['maka2_namn'];
		$maka2->efternamn = empty($input['maka2_efternamn']) ? null : $input['maka2_efternamn'];
    	$maka2->alterPerson($post->id, 'maka2');

    	$post->save();
    	$this->importRepository->cleanUpDatabase();

		return true;

	}



	public function getSizeRows($gard_id, $post_id = null)
	{
        $sql = "SELECT  p.id AS 'post_id',
        		g.id AS 'gard_id',
        		g.namn AS 'gard',
        		s.namn AS 'socken',
        		h.namn AS 'harad',
        		l.namn AS 'landskap',
	            p.natur AS 'natur',
	            sto_herr.mantal AS 'storlek_herrgard_mtl',
	            sto_herr.hektar AS 'storlek_har',
	            sto_herr.aker_hektar AS 'storlek_aker_har',
	            st.namn AS 'status',
	            p.tid_fran AS 'ar_borjan',
	            p.tid_anm AS 'ar_anm',
	            p.agar_arr AS 'agar_arr',
	            p.typ AS 'typ',
	            pe.titel_tjanst AS 'titel_tjanst',
	            pe.titel_familj AS 'titel_familj',
	            pe.namn AS 'namn',
	            pe.efternamn AS 'efternamn',
	            sto_gods.mantal AS 'gods_mtl',
	            sto_gods.hektar AS 'gods_har',
	            sto_gods.aker_hektar AS 'gods_aker_har',
	            sto_gods.taxering AS 'taxering',
	            k.namn AS 'kalla'
            FROM post p
            	JOIN gard g ON p.gard_id = g.id
            	JOIN socken s ON g.socken_id = s.id
            	JOIN harad h ON s.harad_id = h.id
            	JOIN landskap l ON h.landskap_id = l.id
                LEFT JOIN status st ON p.status_id = st.id
                LEFT JOIN person_post ppe ON ppe.post_id = p.id AND ppe.typ = 'person'
                LEFT JOIN person pe ON pe.id = ppe.person_id
                LEFT JOIN storlek sto_herr ON sto_herr.post_id = p.id AND sto_herr.typ = 'herrgard'
                LEFT JOIN storlek sto_gods ON sto_gods.post_id = p.id AND sto_gods.typ = 'gods'
                LEFT JOIN kalla k ON p.kalla_id = k.id
            WHERE ";

            if ( ! is_null($gard_id)) {
            	$sql .= "p.gard_id = ? ";
            	$param = array($gard_id);
            }
            if ( ! is_null($post_id)) {
            	$sql .= "p.id = ? ";
            	$param = array($post_id);
            }
            $sql .= "AND (sto_herr.mantal IS NOT NULL
            		OR sto_herr.hektar IS NOT NULL
            		OR sto_herr.aker_hektar IS NOT NULL
            		OR sto_gods.mantal IS NOT NULL
            		OR sto_gods.hektar IS NOT NULL
            		OR sto_gods.aker_hektar IS NOT NULL
            		OR sto_gods.taxering IS NOT NULL)";

            $sql .= "ORDER BY p.tid_fran";

        $rows = DB::select($sql, $param);
		//echo $sql;exit();
		return $rows;
	}

	public function getSizeRow($post_id)
	{
		$row = $this->getSizeRows(null, $post_id);
		return array_shift($row);
	}


	public function saveSizeRow($input)
	{
		$post = Post::find($input['post_id']);

		$ar_borjan = empty($input['ar_borjan']) ? null : $input['ar_borjan'];

		if ($ar_borjan != $post->tid_fran) {
			$post->tid_fran = $ar_borjan;
		}
		$ar_anm = empty($input['ar_anm']) ? null : $input['ar_anm'];

		if ($ar_anm != $post->tid_anm) {
			$post->tid_anm = $ar_anm;
		}
		$agar_arr = empty($input['agar_arr']) ? null : $input['agar_arr'];

		if ($agar_arr != $post->agar_arr) {
			$post->agar_arr = $agar_arr;
		}
		$typ = empty($input['typ']) ? null : $input['typ'];

		if ($typ != $post->typ) {
			$post->typ = $typ;
		}
		$natur = empty($input['natur']) ? null : $input['natur'];

		if ($natur != $post->natur) {
			$post->natur = $natur;
		}
		$gard = new Gard;
    	$gard_id = $gard->alterGard($input, $post->gard_id);

    	if ($gard_id != $post->gard_id) {
    		$post->gard_id = $gard_id;
    	}
    	$status = new Status;
    	$status_id = $status->alterStatus($input, $post->status_id);

    	if ($status_id != $post->status_id) {
    		$post->status_id = $status_id;
    	}
    	$kalla = new Kalla;
    	$kalla_id = $kalla->alterKalla($input, $post->kalla_id);

    	if ($kalla_id != $post->kalla_id) {
    		$post->kalla_id = $kalla_id;
    	}
		$person = new Person;
		$person->titel_tjanst = empty($input['titel_tjanst']) ? null : $input['titel_tjanst'];
		$person->titel_familj = empty($input['titel_familj']) ? null : $input['titel_familj'];
		$person->namn = empty($input['namn']) ? null : $input['namn'];
		$person->efternamn = empty($input['efternamn']) ? null : $input['efternamn'];
    	$person->alterPerson($post->id, 'person');

    	$storlek_herrgard = new Storlek;

		$storlek_herrgard->mantal = empty($input['storlek_herrgard_mtl']) ? null : $input['storlek_herrgard_mtl'];
		$storlek_herrgard->hektar = empty($input['storlek_har']) ? null : $input['storlek_har'];
		$storlek_herrgard->aker_hektar = empty($input['storlek_aker_har']) ? null : $input['storlek_aker_har'];
		//echo '<pre>'.print_r($storlek_herrgard,true).'</pre>';exit();
    	$storlek_herrgard->alterStorlek($post->id, 'herrgard');

		$storlek_gods = new Storlek;
		$storlek_gods->mantal = empty($input['gods_mtl']) ? null : $input['gods_mtl'];
		$storlek_gods->hektar = empty($input['gods_har']) ? null : $input['gods_har'];
		$storlek_gods->aker_hektar = empty($input['gods_aker_har']) ? null : $input['gods_aker_har'];
		$storlek_gods->taxering = empty($input['taxering']) ? null : $input['taxering'];
    	$storlek_gods->alterStorlek($post->id, 'gods');

    	$post->save();
    	$this->importRepository->cleanUpDatabase();

		return true;

	}



	public function getEstateRows($gard_id, $post_id = null)
	{
        $sql = "SELECT p.id AS 'post_id',
        		g.id AS 'gard_id',
        		g.tillhor_herrgard AS 'tillhor_herrgard',
        		g.namn AS 'gard',
        		s.namn AS 'socken',
        		h.namn AS 'harad',
        		l.namn AS 'landskap',
	            g.nummer AS 'nummer',
	            p.natur AS 'natur',
	 			st.mantal AS 'mantal',
	            st.brukareforhallande AS 'brukareforhallande',
	            pe.titel_tjanst AS 'titel_tjanst',
	            pe.titel_familj AS 'titel_familj',
	            pe.namn AS 'namn',
	            pe.efternamn AS 'efternamn',
	            p.kommentar AS 'kommentar',
	            hg.namn AS 'herrgard',
        		hs.namn AS 'socken_herrgard',
        		hh.namn AS 'harad_herrgard',
        		hl.namn AS 'landskap_herrgard',
        		p.tid_fran AS 'ar',
	            k.namn AS 'kalla'
            FROM post p
            	LEFT JOIN gard g ON p.gard_id = g.id
            	LEFT JOIN socken s ON g.socken_id = s.id
            	LEFT JOIN harad h ON s.harad_id = h.id
            	LEFT JOIN landskap l ON h.landskap_id = l.id
                LEFT JOIN person_post ppe ON ppe.post_id = p.id AND ppe.typ = 'person'
                LEFT JOIN person pe ON pe.id = ppe.person_id
                LEFT JOIN storlek st ON st.post_id = p.id AND st.typ = 'gard'
                LEFT JOIN gard hg ON g.tillhor_herrgard = hg.id
            	LEFT JOIN socken hs ON hg.socken_id = hs.id
            	LEFT JOIN harad hh ON hs.harad_id = hh.id
            	LEFT JOIN landskap hl ON hh.landskap_id = hl.id
                LEFT JOIN kalla k ON p.kalla_id = k.id
            WHERE ";

        if ( ! is_null($gard_id)) {
        	$sql .= "p.gard_id = ? ";

        	$gard = Gard::find($gard_id);

	        if (is_null($gard->tillhor_herrgard)) {
	        	$sql .= "AND g.tillhor_herrgard = ? ";
	        } else {
	        	$sql .= "AND g.id = ? ";
	        }
	        $param = array($gard_id, $gard_id);
        }
        if ( ! is_null($post_id)) {
        	$sql .= "p.id = ? ";
        	$param = array($post_id);
        }


        $sql .= "ORDER BY g.namn";
        $rows = DB::select($sql, $param);

		return $rows;
	}

	public function getEstateRow($post_id)
	{
		$row = $this->getEstateRows(null, $post_id);
		return array_shift($row);
	}


	public function saveEstateRow($input)
	{
		$post = Post::find($input['post_id']);

		$ar_borjan = empty($input['ar']) ? null : $input['ar'];

		if ($ar_borjan != $post->tid_fran) {
			$post->tid_fran = $ar_borjan;
		}
		$natur = empty($input['natur']) ? null : $input['natur'];

		if ($natur != $post->natur) {
			$post->natur = $natur;
		}
		$gard = new Gard;
    	$gard_id = $gard->alterGard($input, $post->gard_id);

    	if ($gard_id != $post->gard_id) {
    		$post->gard_id = $gard_id;
    	}
    	$kalla = new Kalla;
    	$kalla_id = $kalla->alterKalla($input, $post->kalla_id);

    	if ($kalla_id != $post->kalla_id) {
    		$post->kalla_id = $kalla_id;
    	}
		$person = new Person;
		$person->titel_tjanst = empty($input['titel_tjanst']) ? null : $input['titel_tjanst'];
		$person->titel_familj = empty($input['titel_familj']) ? null : $input['titel_familj'];
		$person->namn = empty($input['namn']) ? null : $input['namn'];
		$person->efternamn = empty($input['efternamn']) ? null : $input['efternamn'];
    	$person->alterPerson($post->id, 'person');

    	$storlek_herrgard = new Storlek;

		$storlek_herrgard->mantal = empty($input['mantal']) ? null : $input['mantal'];
		$storlek_herrgard->brukareforhallande = empty($input['brukareforhallande']) ? null : $input['brukareforhallande'];
    	$storlek_herrgard->alterStorlek($post->id, 'gard');

    	$post->save();
    	$this->importRepository->cleanUpDatabase();

		return true;

	}


	public function getDownloadFilename($id)
	{
		$gard = Gard::find($id);

		return Helper::mb_ucfirst($gard->namn);
	}


	public function getMansion($gard_id)
	{
		$gard = Gard::find($gard_id);
		$data['gard_id'] = $gard_id;
		$data['gard'] = $gard->namn;
		$data['nummer'] = $gard->nummer;
		$data['tillhor_herrgard'] = $gard->tillhor_herrgard;
		$data['socken'] = $gard->socken->namn;
		$data['harad'] = $gard->socken->harad->namn;
		$data['landskap'] = $gard->socken->harad->landskap->namn;

		if ( ! is_null($gard->tillhor_herrgard)) {
			$herrgard = Gard::find($gard->tillhor_herrgard);
			$data['herrgard'] = $herrgard->namn;
			$data['socken_herrgard'] = $herrgard->socken->namn;
			$data['harad_herrgard'] = $herrgard->socken->harad->namn;
			$data['landskap_herrgard'] = $herrgard->socken->harad->landskap->namn;
		}
		return $data;
	}

	public function saveMansion($input)
	{
		$gard = Gard::find($input['gard_id']);
		$tillhor_herrgard = $gard->tillhor_herrgard;
    	$gard_id = $gard->updateGard($input);

    	if ($gard_id != $input['gard_id']) {
    		$sql = "UPDATE post SET gard_id = ? WHERE gard_id = ?";
    		$param = [$gard_id,$input['gard_id']];
    		DB::update($sql, $param);
    		$this->importRepository->cleanUpDatabase();
    	}
    	$tmp_gard = Gard::find($gard_id);
    	$tillhor_herrgard_id = $tmp_gard->tillhor_herrgard;

    	if ($tillhor_herrgard_id != $tillhor_herrgard) {
    		$sql = "UPDATE gard SET tillhor_gard = ? WHERE id = ?";
    		$param = [$tillhor_herrgard_id, $gard_id];
    		DB::update($sql, $param);
    		$this->importRepository->cleanUpDatabase();
    	}
    	return true;
	}
}
