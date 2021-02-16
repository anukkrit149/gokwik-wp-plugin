<?php
/********************
 * Developed by Anukkrit Shanker
 * Time-02:14 AM
 * Date-05-02-2021
 * File-Error.php
 * Project-gokwik-php-sdk
 * Copyrights Reserved
 * Created by PhpStorm
 *
 * Working-
 *********************/

namespace Gokwik\Api\Errors;

use Exception;

class Error extends Exception
{
    protected $httpStatusCode;

    public function __construct($message, $code, $httpStatusCode)
    {
        $this->code = $code;

        $this->message = $message;

        $this->httpStatusCode = $httpStatusCode;
    }

    public function getHttpStatusCode()
    {
        return $this->httpStatusCode;
    }
}

