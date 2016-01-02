<?php

namespace HRDNS\Core;

class Event implements EventInterface
{

    protected $propagation = true;

    public function stopPropagation()
    {
        $this->propagation = false;
    }

    public function getPropagationStatus()
    {
        return $this->propagation;
    }

}
