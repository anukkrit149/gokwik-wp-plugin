<?php
/********************
 * Developed by Anukkrit Shanker
 * Time-01:57 AM
 * Date-08-02-2021
 * File-Resource.php
 * Project-gokwik-php-sdk
 * Copyrights Reserved
 * Created by PhpStorm
 *
 * Working-
 *********************/

namespace Gokwik\Api;

use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;

class Resource implements ArrayAccess, IteratorAggregate
{
    protected $attributes = array();

    public function getIterator()
    {
        return new ArrayIterator($this->attributes);
    }

    public function offsetExists($offset)
    {
        return (isset($this->attributes[$offset]));
    }

    public function offsetSet($offset, $value)
    {
        $this->attributes[$offset] = $value;
    }

    public function offsetGet($offset)
    {
        return $this->attributes[$offset];
    }

    public function offsetUnset($offset)
    {
        unset($this->attributes[$offset]);
    }

    public function __get($key)
    {
        return $this->attributes[$key];
    }

    public function __set($key, $value)
    {
        return $this->attributes[$key] = $value;
    }

    public function __isset($key)
    {
        return (isset($this->attributes[$key]));
    }

    public function __unset($key)
    {
        unset($this->attributes[$key]);
    }
}


