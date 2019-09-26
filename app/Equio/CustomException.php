<?php
namespace App\Equio;


use Mockery\Exception;
use Illuminate\Http\Request;

class CustomException extends Exception {


    public $message;
    public $code;
    public $severity;
    public $file;
    public $line;

   public function __construct($message=null, $code=null, $severity=null, $filename=null, $lineno=null) {

        $this->message = $message;
        $this->code = $code;
        $this->severity = $severity;
        $this->file = $filename;
        $this->line = $lineno;

    }

    public function errorMessage() {

        return $this->message . " line no: " .$this->line . " of " .$this->file;
    }

}


