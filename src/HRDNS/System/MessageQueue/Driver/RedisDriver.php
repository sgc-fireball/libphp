<?php

namespace HRDNS\System\MessageQueue\Driver;

class RedisDriver implements DriverInterface
{

    const PREFIX = 'hrdns:messagequeue';

    /** @var \Redis */
    private $redis;

    /** @var array */
    private $queues = [];

    /**
     * @param \Redis $redis
     */
    public function __construct(\Redis $redis)
    {
        $this->redis = $redis;
    }

    /**
     * @param string $name
     * @return DriverInterface
     */
    public function init(string $name): DriverInterface
    {
        $key = sprintf('%s:%s:index', self::PREFIX, $name);
        $this->queues[$name] = $this->redis->get($key);
        if ($this->queues[$name] === false) {
            $this->queues[$name] = 0;
            $this->redis->set($key, $this->queues[$name]);
        }
        $this->queues[$name]++;
        return $this;
    }

    /**
     * @param string $name
     * @param array $message
     * @return DriverInterface
     */
    public function add(string $name, array $message): DriverInterface
    {
        $key = sprintf('%s:%s:%d', self::PREFIX, $name, $this->getNextIndex($name));
        $this->redis->set($key, json_encode($message));
        return $this;
    }

    /**
     * @param string $name
     * @return array|null
     */
    public function next(string $name)
    {
        $result = $this->redis->get(sprintf('%s:%s:%d', self::PREFIX, $name, $this->queues[$name]));
        if (!$result) {
            return null;
        }
        $this->queues[$name]++;
        return json_decode($result, true);
    }

    /**
     * @param string $name
     * @return integer
     */
    private function getNextIndex(string $name): int
    {
        $key = sprintf('%s:%s:index', self::PREFIX, $name);
        $index = (int)$this->redis->incr($key);
        return $index;
    }

}
