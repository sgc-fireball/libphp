<?php

namespace HRDNS\Types\Stack;

use HRDNS\Types\Stack;

class FILO extends Stack
{

    public function key()
    {
        return ($this->count(true) - $this->position);
    }

    public function pop()
    {
        return array_pop($this->elements);
    }

}
