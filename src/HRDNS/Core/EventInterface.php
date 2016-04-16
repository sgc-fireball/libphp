<?php

namespace HRDNS\Core;

interface EventInterface
{

    /**
     * @return EventInterface
     */
    public function stopPropagation(): EventInterface;

    /**
     * @return bool
     */
    public function getPropagationStatus(): bool;

}
