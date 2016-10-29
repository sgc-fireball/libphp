<?php

namespace HRDNS\Core;

class Event implements EventInterface
{

    /** @var bool */
    protected $propagation = true;

    /**
     * @return EventInterface
     */
    public function stopPropagation(): EventInterface
    {
        $this->propagation = false;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isPropagationStopped(): bool
    {
        return $this->propagation;
    }

}
