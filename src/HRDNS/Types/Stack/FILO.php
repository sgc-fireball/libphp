<?php declare(strict_types=1);

namespace HRDNS\Types\Stack;

use HRDNS\Types\Stack;

class FILO extends Stack
{

    /**
     * @return integer
     */
    public function key(): int
    {
        return ($this->count() - 1 - $this->position);
    }

    /**
     * @return mixed
     */
    public function pop()
    {
        return array_pop($this->elements);
    }

}
