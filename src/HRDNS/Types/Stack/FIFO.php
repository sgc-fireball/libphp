<?php declare(strict_types=1);

namespace HRDNS\Types\Stack;

use HRDNS\Types\Stack;

class FIFO extends Stack
{

    /**
     * @return integer
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
        return array_shift($this->elements);
    }

}
