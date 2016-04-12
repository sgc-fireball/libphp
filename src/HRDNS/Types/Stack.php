<?php

namespace HRDNS\Types;

/**
 * Class Stack
 *
 * @package HRDNS\Types
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
abstract class Stack implements \Iterator
{

    /**
     * @var integer
     */
    protected $position = 0;

    /**
     * @var array
     */
    protected $elements = array();

    /**
     * @param array $elements
     */
    public function __construct(array $elements = array())
    {
        $this->position = 0;
        foreach ($elements as $element) {
            $this->push($element);
        }
    }

    /**
     * @return mixed
     */
    public function current()
    {
        return $this->elements[$this->key()];
    }

    /**
     * @return void
     */
    public function next()
    {
        $this->position++;
    }

    /**
     * @return mixed
     */
    abstract public function key();

    /**
     * @return boolean
     */
    public function valid()
    {
        return isset($this->elements[$this->position]);
    }

    /**
     * @return void
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * @param mixed $element
     * @return void
     */
    public function push($element)
    {
        $this->elements[] = $element;
    }

    /**
     * @abstract
     * @return mixed
     */
    abstract public function pop();

    /**
     * @return mixed
     */
    public function shift()
    {
        return $this->pop();
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
        unset($this->elements[$key]);
        $this->elements = array_values($this->elements);
    }

    /**
     * @param boolean $array
     * @return integer
     * @SuppressWarnings(PHPMD.boolArgumentFlag)
     */
    public function count(bool $array = false)
    {
        $count = count($this->elements);
        return $array ? $count - 1 : $count;
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        return array('elements', 'position');
    }

}
