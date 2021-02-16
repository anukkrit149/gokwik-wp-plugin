<?php
/********************
 * Developed by Anukkrit Shanker
 * Time-02:34 PM
 * Date-05-02-2021
 * File-Order.php
 * Project-gokwik-php-sdk
 * Copyrights Reserved
 * Created by PhpStorm
 *
 *
 * Working-
 *********************/


namespace Gokwik\Api;


class Order extends Entity{
    function create($attributes = array())
    {
        echo "Order create function\n";
        return parent::create($attributes);
    }

    function update($attributes = array())
    {
        echo "Order Update function\n";
        return parent::update($attributes);
    }

}