<?php
/********************
 * Developed by Anukkrit Shanker
 * Time-02:17 AM
 * Date-05-02-2021
 * File-ErrorCode.php
 * Project-gokwik-php-sdk
 * Copyrights Reserved
 * Created by PhpStorm
 *
 * Working-
 *********************/

namespace Gokwik\Api\Errors;

class ErrorCode
{
    const BAD_REQUEST_ERROR                 = 'BAD_REQUEST_ERROR';
    const SERVER_ERROR                      = 'SERVER_ERROR';
    const GATEWAY_ERROR                     = 'GATEWAY_ERROR';

    public static function exists($code)
    {
        $code = strtoupper($code);

        return defined(get_class() . '::' . $code);
    }
}

