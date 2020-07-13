<?php declare(strict_types=1);

namespace HRDNS\Core;

interface EventInterface
{

    /**
     * @return EventInterface
     */
    public function stopPropagation(): EventInterface;

    /**
     * @return boolean
     */
    public function isPropagationStopped(): bool;

}
