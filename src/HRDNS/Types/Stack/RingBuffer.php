<?php

namespace HRDNS\Types\Stack;

use HRDNS\Types\Stack;

class RingBuffer extends Stack
{

    /** @var integer */
    protected $size = 1024;

    /** @var integer */
    protected $index = 0;

    /**
     * @param integer $size
     * @param array $elements
     */
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

    /**
     * @param mixed $element
     * @return void
     */
    public function push($element = null)
    {
        $this->elements[$this->index++] = $element;
        $this->index = $this->index >= $this->size ? 0 : $this->index;
    }

    /**
     * @return integer
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * @return mixed
     */
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

    /**
     * @param mixed $element
     * @return void
     */
    public function remove($element)
    {
        $key = array_search($element, $this->elements, true);
        if ($key === false) {
            return;
        }
        $this->elements[$key] = null;
        $this->elements = array_values($this->elements);
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        $array = parent::__sleep();
        $array += array ('size', 'in');

        return $array;
    }

}
