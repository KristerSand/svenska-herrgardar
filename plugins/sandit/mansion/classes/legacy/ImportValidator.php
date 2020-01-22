<?php  namespace Sandit\Mansion\Classes;


class ImportValidator extends Illuminate\Validation\Validator
{

    public function validateColumns($attribute, $value, $parameters)
    {
        return $value == 'foo';
    }

}
