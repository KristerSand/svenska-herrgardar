<?php  namespace Sandit\Mansion\Classes;

class Helper {

	public static function mb_ucfirst($str) {

		if ( ! $str) {
			return $str;
		}
	    $fc = mb_strtoupper(mb_substr($str, 0, 1));
	    return $fc.mb_substr($str, 1);
	}


	public static function print_r_pre($array)
	{
		echo '<pre>'.print_r($array, true).'</pre>';
	}
}