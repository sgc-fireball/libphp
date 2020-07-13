<?php declare(strict_types=1);

namespace HRDNS\System\MessageQueue\Driver;

use HRDNS\Types\Stack\FIFO;

class ArrayDriver implements DriverInterface
{

    /** @var FIFO[] */
    private $queues = [];

    /**
     * @param string $name
     * @param array $message
     * @return DriverInterface
     */
    public function add(string $name, array $message): DriverInterface
    {
        $this->checkQueue($name);
        $this->queues[$name]->push($message);
        return $this;
    }

    /**
     * @param string $name
     * @return array|null
     */
    public function next(string $name)
    {
        $this->checkQueue($name);
        if (!$this->queues[$name]->valid()) {
            return null;
        }
        return $this->queues[$name]->pop();
    }

    /**
     * @param string $name
     * @return DriverInterface
     */
    public function init(string $name): DriverInterface
    {
        $this->checkQueue($name);
        return $this;
    }

    /**
     * @param string $name
     * @return void
     */
    private function checkQueue(string $name)
    {
        if (!isset($this->queues[$name])) {
            $this->queues[$name] = new FIFO();
        }
    }

}
