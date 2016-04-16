<?php

namespace HRDNS\Types\Stack;

use HRDNS\Types\Stack;

class RingBuffer extends Stack
{

    /** @var int */
    protected $size = 1024;

    /** @var int */
    protected $index = 0;

    /**
     * @param int $size
     * @param array $elements
     */
    public function __construct(int $size = 1024, array $elements = [])
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
     * @return Stack
     */
    public function push($element): Stack
    {
        $this->elements[$this->index++] = $element;
        $this->index = $this->index >= $this->size ? 0 : $this->index;
        return $this;
    }

    /**
     * @return int
     */
    public function key(): int
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
     * @return Stack
     */
    public function remove($element): Stack
    {
        $key = array_search($element, $this->elements, true);
        if ($key === false) {
            return;
        }
        $this->elements[$key] = null;
        $this->elements = array_values($this->elements);
        return $this;
    }

    /**
     * @return array
     */
    public function __sleep(): array
    {
        $array = parent::__sleep();
        $array += ['size', 'in'];
        return $array;
    }

}
