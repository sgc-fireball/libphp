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
     * @var int
     */
    protected $position = 0;

    /**
     * @var array
     */
    protected $elements = [];

    /**
     * @param array $elements
     * @return void
     */
    public function __construct(array $elements = [])
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
    public function valid(): bool
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
     * @return self
     */
    public function push($element): self
    {
        $this->elements[] = $element;
        return $this;
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
     * @return self
     */
    public function remove($element): self
    {
        $key = array_search($element, $this->elements, true);
        if ($key === false) {
            return $this;
        }
        unset($this->elements[$key]);
        $this->elements = array_values($this->elements);
        return $this;
    }

    /**
     * @return integer
     * @SuppressWarnings(PHPMD.boolArgumentFlag)
     */
    public function count(): int
    {
        return count($this->elements);
    }

    /**
     * @return array
     */
    public function __sleep(): array
    {
        return ['elements', 'position'];
    }

}
