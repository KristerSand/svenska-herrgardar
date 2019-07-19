<?php namespace Sandit\Mansion\Models;

use Model;
//use Sandit\Mansion\Models\Post;
USE DB;

/**
 * Model
 */
class Person extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    protected $fillable = ['namn', 'efternamn','titel_tjanst','titel_familj'];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'sandit_mansion_person';

    protected $hidden = [
        'id',
        'created_at',
        'updated_at'
    ];

    public $hasMany = [
        'post' => ['Sandit\Mansion\Models\Post']
    ];

    public static function getTitles($type) 
    {
        if (in_array($type, ['person_titel_tjanst','person_titel_familj'])) {
            $person_col = $type == 'person_titel_tjanst' ? 'titel_tjanst' : 'titel_familj';
            $query = "  SELECT 
                            DISTINCT CONCAT(UCASE(MID(pe.".$person_col." ,1,1)),MID(pe.".$person_col." ,2)) AS titel
                        FROM sandit_mansion_person pe
                            JOIN sandit_mansion_post po ON pe.id=po.agare_person_id
                        WHERE pe.".$person_col." IS NOT NULL
                        ORDER BY titel";
        } elseif (in_array($type, ['maka_titel_tjanst','maka_titel_familj'])) {
            $person_col = $type == 'maka_titel_tjanst' ? 'titel_tjanst' : 'titel_familj';
            $query = "  SELECT DISTINCT CONCAT(UCASE(MID(".$person_col." ,1,1)),MID(".$person_col." ,2)) AS titel
                        FROM    
                        (SELECT pe1.".$person_col."
                        FROM sandit_mansion_person pe1
                            JOIN sandit_mansion_post po ON pe1.id=po.maka1_person_id
                        WHERE pe1.".$person_col." IS NOT NULL
                        UNION 
                        SELECT  pe2.".$person_col."
                        FROM sandit_mansion_person pe2
                            JOIN sandit_mansion_post po ON pe2.id=po.maka2_person_id
                        WHERE pe2.".$person_col." IS NOT NULL) AS tmp
                        ORDER BY titel";
        } else {
            return [];
        }
        return DB::select($query);
    }


    /*public function alterPerson($post_id, $type)
    {
        $post_persons = DB::table('person_post')->
            where('post_id','=',$post_id)->
            where('typ','=',$type)->get();

        if (empty($post_persons) &&
            is_null($this->namn) &&
            is_null($this->efternamn) &&
            is_null($this->titel_tjanst) &&
            is_null($this->titel_familj)) {
            return;
        }

        if (empty($post_persons)) {
            $new_person = new Person;
            $new_person->namn = $this->namn;
            $new_person->efternamn = $this->efternamn;
            $new_person->titel_tjanst = $this->titel_tjanst;
            $new_person->titel_familj = $this->titel_familj;
            $new_person->save();

            DB::table('person_post')->
                insert(['post_id' => $post_id,
                    'person_id' => $new_person->id,
                    'typ' => $type]);
            return;
        }

        $post_person = array_shift($post_persons);
        $person_1 = Person::find($post_person->person_id);

        $post_persons = DB::table('person_post')->
            where('person_id','=',$person_1->id)->get();

        if (is_null($this->namn) && is_null($this->efternamn) && is_null($this->titel_tjanst) && is_null($this->titel_familj)) {
            DB::table('person_post')->where('id','=',$post_person->id)->delete();

            if (count($post_persons) == 1) {
                $person_1->delete();
            }
            return;
        }
        $person = DB::table('sandit_mansion_person')->where('namn','=',$this->namn)->
            where('efternamn','=',$this->efternamn)->
            where('titel_tjanst','=',$this->titel_tjanst)->
            where('titel_familj','=',$this->titel_familj)->get();

        if ( ! empty($person)) {
            $person_2 = array_shift($person);

            if ($person_2 && ($person_1->id != $person_2->id)) {
                DB::table('person_post')->
                    where('id',$post_person->id)->
                    update(array('person_id' => $person_2->id));

                if (count($post_persons) == 1) {
                    $person_1->delete();
                }
            }
            return;
        }
        if (count($post_persons) == 1) {
            $person_1->namn = $this->namn;
            $person_1->efternamn = $this->efternamn;
            $person_1->titel_tjanst = $this->titel_tjanst;
            $person_1->titel_familj = $this->titel_familj;
            $person_1->save();
        } else {
            $new_person = new Person;
            $new_person->namn = $this->namn;
            $new_person->efternamn = $this->efternamn;
            $new_person->titel_tjanst = $this->titel_tjanst;
            $new_person->titel_familj = $this->titel_familj;
            $new_person->save();

            DB::table('person_post')->
                where('id',$post_person->id)->
                update(array('person_id' => $new_person->id));
        }
    }*/

}
