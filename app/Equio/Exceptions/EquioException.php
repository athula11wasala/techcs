<?php

namespace App\Equio\Exceptions;

/**
 * Class EquioException
 * @package App\Equio\Exceptions
 */
class EquioException extends \Exception
{

    /**
     * EquioException constructor.
     * @param string $message
     * @param int $code
     */
    public function __construct($message, $code = 0)
    {
        parent::__construct($message, $code);
    }

}