<?php namespace Sandit\Mansion\Models;

use Model;
use Input;
use DB;
use Sandit\Mansion\Models\Post;

/**
 * Model
 */
class Gard extends Model
{
    use \October\Rain\Database\Traits\Validation;

    protected $fillable = array(
        'id',
        'namn',
        'socken_id',
        'toraid'
    );

    /**
    * Hidden for the api
    */
    protected $hidden = [
        'socken_id',
        'tillhor_herrgard',
        'nummer',
        'created_at',
        'updated_at'
    ];

    /**
     * @var array Validation rules
     */
    public $rules = [
        'namn' => 'required'
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'sandit_mansion_gard';

    public $belongsTo = [
        'socken' => ['Sandit\Mansion\Models\Socken'],
    ];

    public $hasOneThrough = [
        'harad' => ['Sandit\Mansion\Models\Harad', 'Sandit\Mansion\Models\Socken'],
        'landskap' => ['Sandit\Mansion\Models\Landskap', 'Sandit\Mansion\Models\Harad'],
    ];

    public $hasMany = [
        'post' => ['Sandit\Mansion\Models\Post'],
    ];


    static function getGardPosts($id)
    {
        //dd($id);
        $gard = Gard::with('socken.harad.landskap')->where('id','=',$id)->first();
        $gard->poster = Post::with('jordnatur','agare','maka1','maka2','status','kalla')
            ->where('gard_id','=',$id)
            ->get();

        return $gard;
    }


    public function getGardId($param)
    {
        $socken = new Socken;
        $socken_id = $socken->get_socken_id($param);

        /*if ( ! isset($param['tillhor_herrgard'])) {
            $param['tillhor_herrgard'] = null;
        }
        if ( ! isset($param['nummer'])) {
            $param['nummer'] = null;
        }*/
        if ( ! isset($param['toraid'])) {
            $param['toraid'] = null;
        }
        $result = DB::table('sandit_mansion_gard')
                ->where('id', '=', $param['id'])
                //->where('toraid', '=', $param['toraid'])
                //->where('namn', '=', $param['gard'])
                //->where('socken_id', '=', $socken_id)
                /*->where('tillhor_herrgard','=', $param['tillhor_herrgard'])
                ->where('nummer','=', $param['nummer'])*/
                ->get();

        if ($result->isNotEmpty()) {
            return $result->first()->id;
        }
        $this->id = $param['id'];
        $this->toraid = $param['toraid'];
        $this->namn = $param['gard'];
        $this->socken_id = $socken_id;
        /*$this->tillhor_herrgard = $param['tillhor_herrgard'];
        $this->nummer = $param['nummer'];*/
        $this->save();

        return $param['id'];
    }


    /*public function saveGard($data)
    {
        if (isset($data['herrgard'])) {

            if (is_null($data['herrgard'])) {
                $data['herrgard'] = '';
            }
            if (is_null($data['socken_herrgard'])) {
                $data['socken_herrgard'] = '';
            }
            if (is_null($data['harad_herrgard'])) {
                $data['harad_herrgard'] = '';
            }
            if (is_null($data['landskap_herrgard'])) {
                $data['landskap_herrgard'] = '';
            }
            $herrgard = new Gard;
            $param_herrgard = array('gard' => $data['herrgard'],
                            'socken' => $data['socken_herrgard'],
                            'harad' => $data['harad_herrgard'],
                            'landskap' => $data['landskap_herrgard']);
            $herrgard_id = $herrgard->get_gard_id($param_herrgard);
        }
        $gard = new Gard;
        $param_gard = array('gard' => $data['gard'],
                            'socken' => $data['socken'],
                            'harad' => $data['harad'],
                            'landskap' => $data['landskap']);
        if (isset($herrgard_id)) {
            $param_gard['tillhor_herrgard'] = $herrgard_id;
            $param_gard['nummer'] = $data['nr'];

        }

        return $gard->get_gard_id($param_gard);
    }*/


    public function alterGard($input, $gard_id)
    {
        $gard = new Gard;
        $gard = Gard::find($gard_id);
        $socken = $gard->socken;
        $harad = $gard->socken->harad;
        $landskap = $gard->socken->harad->landskap;

        $data['gard'] = empty($input['gard']) ? null : $input['gard'];
        $data['socken'] = empty($input['socken']) ? null : $input['socken'];
        $data['harad'] = empty($input['harad']) ? null : $input['harad'];
        $data['landskap'] = empty($input['landskap']) ? null : $input['landskap'];
        $data['nummer'] = ( ! isset($input['nummer']) || empty($input['nummer'])) ? null : $input['nummer'];
        $data['tillhor_herrgard'] = ( ! isset($input['herrgard'])) ? null : $this->alterHerrgard($input, $gard->tillhor_herrgard);

        if (($data['gard'] != $gard->namn || $data['tillhor_herrgard'] != $gard->tillhor_herrgard || $data['nummer'] != $gard->nummer)
            && $data['socken'] == $socken->namn
            && $data['harad'] == $harad->namn
            && $data['landskap'] == $harad->namn) {
            $gard->namn = $data['gard'];
            $gard->tillhor_herrgard = $data['tillhor_herrgard'];
            $gard->nummer = $data['nummer'];
            $gard->save();

            return $gard_id;
        }
        return  $gard->get_gard_id($data);
    }


    public function alterHerrgard($input, $gard_id)
    {
        $data['gard'] = empty($input['herrgard']) ? null : $input['herrgard'];
        $data['socken'] = empty($input['socken_herrgard']) ? null : $input['socken_herrgard'];
        $data['harad'] = empty($input['harad_herrgard']) ? null : $input['harad_herrgard'];
        $data['landskap'] = empty($input['landskap_herrgard']) ? null : $input['landskap_herrgard'];

        $gard = new Gard;
        $gard = Gard::find($gard_id);
        $socken = $gard->socken;
        $harad = $gard->socken->harad;
        $landskap = $gard->socken->harad->landskap;

        if ($data['gard'] != $gard->namn
            && $data['socken'] == $socken->namn
            && $data['harad'] == $harad->namn
            && $data['landskap'] == $harad->namn) {
            $gard->namn = $data['gard'];

            $gard->save();

            return $gard_id;
        }
        return  $gard->get_gard_id($data);
    }


    public function updateGard($input)
    {
        $data['gard'] = empty($input['gard']) ? null : $input['gard'];
        $data['socken'] = empty($input['socken']) ? null : $input['socken'];
        $data['harad'] = empty($input['harad']) ? null : $input['harad'];
        $data['landskap'] = empty($input['landskap']) ? null : $input['landskap'];
        $data['nummer'] = ( ! isset($input['nummer']) || empty($input['nummer'])) ? null : $input['nummer'];
        //$data['tillhor_herrgard'] = ( ! isset($input['herrgard'])) ? null : $this->alterHerrgard($input, $gard->tillhor_herrgard);
        $data['herrgard'] = ( ! isset($input['herrgard']) || empty($input['herrgard'])) ? null : $input['herrgard'];
        $data['socken_herrgard'] = ( ! isset($input['socken_herrgard']) || empty($input['socken_herrgard'])) ? null : $input['socken_herrgard'];
        $data['harad_herrgard'] = ( ! isset($input['harad_herrgard']) || empty($input['harad_herrgard'])) ? null : $input['harad_herrgard'];
        $data['landskap_herrgard'] = ( ! isset($input['landskap_herrgard']) || empty($input['landskap_herrgard'])) ? null : $input['landskap_herrgard'];

        $table_prefix = 'sandit_mansion_';

        $result = DB::table('sandit_mansion_gard')->
            join('sandit_mansion_socken','gard.socken_id','=','socken.id')->
            join('sandit_mansion_harad','socken.harad_id','=','harad.id')->
            join('sandit_mansion_landskap','harad.landskap_id','=','landskap.id')->
            leftjoin('sandit_mansion_gard AS herrgard','gard.tillhor_herrgard','=','herrgard.id')->
            leftjoin('sandit_mansion_socken AS socken_herrgard','herrgard.socken_id','=','socken_herrgard.id')->
            leftjoin('sandit_mansion_harad AS harad_herrgard','socken_herrgard.harad_id','=','harad_herrgard.id')->
            leftjoin('sandit_mansion_landskap AS landskap_herrgard','harad_herrgard.landskap_id','=','landskap_herrgard.id')->
            select('gard.id')->
            where('gard.namn',$data['gard'])->
            where('gard.nummer',$data['nummer'])->
            where('socken.namn',$data['socken'])->
            where('harad.namn',$data['harad'])->
            where('landskap.namn',$data['landskap'])->
            where('herrgard.namn',$data['herrgard'])->
            where('socken_herrgard.namn',$data['socken_herrgard'])->
            where('harad_herrgard.namn',$data['harad_herrgard'])->
            where('landskap_herrgard.namn',$data['landskap_herrgard'])->first();

        if ($result && $result->id != $input['gard_id']) {
            return $result->id;
        }
        $gard = Gard::find($input['gard_id']);
        $gard->namn = $data['gard'];
        $gard->nummer = $data['nummer'];
        $gard->tillhor_herrgard = $this->alterHerrgard($input, $gard->tillhor_herrgard);
        $socken = new Socken;
        $gard->socken_id = $socken->get_socken_id($data);

        $gard->save();
        return $gard->id;
    }

}
