<?php

namespace HRDNS\Types\Stack;

use HRDNS\Types\Stack;

class FILO extends Stack
{

    /**
     * @return int
     */
    public function key(): int
    {
        return ($this->count(true) - $this->position);
    }

    /**
     * @return mixed
     */
    public function pop()
    {
        return array_pop($this->elements);
    }

}
