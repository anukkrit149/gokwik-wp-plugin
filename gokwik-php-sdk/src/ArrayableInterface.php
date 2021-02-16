<?php
/********************
 * Developed by Anukkrit Shanker
 * Time-01:56 AM
 * Date-08-02-2021
 * File-ArrayableInterface.php
 * Project-gokwik-php-sdk
 * Copyrights Reserved
 * Created by PhpStorm
 *
 * Working-
 *********************/

namespace Gokwik\Api;

interface ArrayableInterface
{
    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray();
}



