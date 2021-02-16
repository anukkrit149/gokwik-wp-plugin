<?php
/********************
 * Developed by Anukkrit Shanker
 * Time-02:16 AM
 * Date-05-02-2021
 * File-BadRequestError.php
 * Project-gokwik-php-sdk
 * Copyrights Reserved
 * Created by PhpStorm
 *
 * Working-
 *********************/

namespace Gokwik\Api\Errors;


class BadRequestError extends Error
{
    protected $field = null;

    public function __construct($message, $code, $httpStatusCode, $field = null)
    {
        parent::__construct($message, $code, $httpStatusCode);

        $this->field = $field;
    }

    public function getField()
    {
        return $this->field;
    }
}