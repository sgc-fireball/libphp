<?php

namespace HRDNS\Core;

interface EventInterface
{

    public function stopPropagation();

    public function getPropagationStatus();

}
