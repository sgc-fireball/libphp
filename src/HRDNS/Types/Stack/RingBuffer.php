<?php declare(strict_types=1);

namespace HRDNS\Types\Stack;

use HRDNS\Types\Stack;

class RingBuffer extends Stack
{

    /** @var int */
    protected $size = 1024;

    /** @var int */
    protected $index = 0;

    public function __construct(int $size = 1024, array $elements = [])
    {
        $this->size = (int)$size;
        $this->size = $this->size < 1 ? 1 : $this->size;
        parent::__construct($elements);
        $count = $this->count();
        for ($i = $count; $i < $this->size; $i++) {
            $this->elements[$i] = null;
        }
    }

    public function push($element): Stack
    {
        $this->elements[$this->index++] = $element;
        $this->index = $this->index >= $this->size ? 0 : $this->index;
        return $this;
    }

    public function key(): int
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

    public function remove($element): Stack
    {
        $key = array_search($element, $this->elements, true);
        if ($key === false) {
            return $this;
        }
        $this->elements[$key] = null;
        $this->elements = array_values($this->elements);
        return $this;
    }

    public function __sleep(): array
    {
        $array = parent::__sleep();
        $array[] = 'size';
        $array[] = 'in';
        return $array;
    }

}
