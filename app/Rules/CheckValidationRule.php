<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;


class CheckValidationRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    protected $attribute;
    protected $passAttr;


    public function __construct($passAttr = null)
    {
        $this->passAttr = $passAttr;

    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {


      if(isset($_POST['profile_type'])  &&  $_POST['profile_type']  == 1 ){

          $value = str_replace(":","",$value);
          if ( $attribute == "ticker" && (!ctype_upper ( $value )) ) {

              $this->attribute = $attribute;

              return false;
          } else {
              return true;
          }
      }

        return true;


    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        if ( $this->attribute == "ticker" ) {
            return 'The ticker  does not consist of all uppercase letters.';
        }

    }
}
