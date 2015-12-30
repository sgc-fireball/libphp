<?php

namespace HRDNS\Types\Stack;

use HRDNS\Types\Stack;

class RingBuffer extends Stack
{

    protected $size = 1024;
    protected $in = 0;

    public function __construct($size = 1024, array $elements = array ())
    {
        $this->size = (int)$size;
        $this->size = $this->size < 1 ? 1 : $this->size;
        parent::__construct($elements);
        $count = $this->count();
        for ($i = $count ; $i < $this->size ; $i++) {
            $this->elements[$i] = null;
        }
    }

    public function push($element = null)
    {
        $this->elements[$this->in++] = $element;
        $this->in = $this->in >= $this->size ? 0 : $this->in;
    }

    public function key()
    {
        return $this->position;
    }

    public function pop()
    {
        if (!isset($this->elements[$this->position])) {
            $this->position = 0;
        }
        $element = $this->elements[$this->position];
        $this->elements[$this->position++] = null;
        $this->position = $this->position >= $this->size ? 0 : $this->position;

        return $element;
    }

    public function remove($element)
    {
        $key = array_search($element, $this->elements, true);
        if ($key === false) {
            return;
        }
        $this->elements[$key] = null;
        $this->elements = array_values($this->elements);
    }

    public function __sleep()
    {
        $array = parent::__sleep();
        $array += array ('size', 'in');

        return $array;
    }

}
