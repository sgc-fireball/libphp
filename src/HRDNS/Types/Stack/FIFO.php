<?php

namespace HRDNS\Types\Stack;

use HRDNS\Types\Stack;

class FIFO extends Stack
{

    /**
     * @return integer
     */
    public function key(): mixed
    {
        return $this->position;
    }

    /**
     * @return mixed
     */
    public function pop()
    {
        return array_shift($this->elements);
    }

}
