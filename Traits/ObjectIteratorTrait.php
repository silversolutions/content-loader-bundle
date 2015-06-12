<?php

namespace Siso\Bundle\ContentLoaderBundle\Traits;

/**
 * Trait for object iterators.
 * @see: http://php.net/manual/en/language.oop5.iterations.php
 */
trait ObjectIteratorTrait
{
    private $items = [];
    
    public function rewind()
    {
        reset($this->items);
    }

    public function current()
    {
        $var = current($this->items);
        return $var;
    }

    public function key()
    {
        $var = key($this->items);
        return $var;
    }

    public function next()
    {
        $var = next($this->items);
        return $var;
    }

    public function valid()
    {
        $key = key($this->items);
        $var = ($key !== NULL && $key !== FALSE);
        return $var;
    }
}