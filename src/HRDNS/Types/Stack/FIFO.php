<?php

namespace HRDNS\Types\Stack;

use HRDNS\Types\Stack;

class FIFO extends Stack
{

    public function key()
    {
        return $this->position;
    }

    public function pop()
    {
        return array_shift($this->elements);
    }

}
