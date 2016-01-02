<?php

namespace HRDNS\Types;

abstract class Stack implements \Iterator
{

    protected $position = 0;

    protected $elements = array ();

    public function __construct(array $elements = array ())
    {
        $this->position = 0;
        foreach ($elements as $element) {
            $this->push($element);
        }
    }

    public function current()
    {
        return $this->elements[$this->key()];
    }

    public function next()
    {
        $this->position++;
    }

    abstract public function key();

    public function valid()
    {
        return isset($this->elements[$this->position]);
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function push($element)
    {
        $this->elements[] = $element;
    }

    abstract public function pop();

    public function shift()
    {
        return $this->pop();
    }

    public function remove($element)
    {
        $key = array_search($element, $this->elements, true);
        if ($key === false) {
            return;
        }
        unset($this->elements[$key]);
        $this->elements = array_values($this->elements);
    }

    public function count($array = false)
    {
        $count = count($this->elements);
        return $array ? $count - 1 : $count;
    }

    public function __sleep()
    {
        return array ('elements', 'position');
    }

}
