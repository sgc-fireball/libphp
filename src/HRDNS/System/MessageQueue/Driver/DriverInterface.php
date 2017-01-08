<?php

namespace HRDNS\System\MessageQueue\Driver;

interface DriverInterface
{

    /**
     * @param string $name
     * @param array $message
     * @return DriverInterface
     */
    public function add(string $name, array $message): DriverInterface;

    /**
     * @param string $name
     * @return mixed
     */
    public function next(string $name);

    /**
     * @param string $name
     * @return DriverInterface
     */
    public function init(string $name): DriverInterface;

}
