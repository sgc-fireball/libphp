<?php

namespace HRDNS\System\MessageQueue;

use HRDNS\System\MessageQueue\Driver\ArrayDriver;
use HRDNS\System\MessageQueue\Driver\DriverInterface;

class MessageQueue implements MessageQueueInterface
{

    /** @var string */
    private $name;

    /** @var DriverInterface */
    private $driver;

    /**
     * @param string $name
     * @param DriverInterface $driver
     */
    public function __construct(string $name, DriverInterface $driver = null)
    {
        $this->name = $name;
        $this->driver = $driver ?? new ArrayDriver();
        $this->driver->init($this->name);
    }

    /**
     * @param array $message
     * @return MessageQueueInterface
     */
    public function add(array $message): MessageQueueInterface
    {
        $this->driver->add($this->name, $message);
        return $this;
    }

    /**
     * @return array|null
     */
    public function next()
    {
        return $this->driver->next($this->name);
    }

}
