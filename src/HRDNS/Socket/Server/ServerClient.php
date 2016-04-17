<?php

namespace HRDNS\Socket\Server;

/**
 * Class ServerClient
 *
 * @package HRDNS\Socket\Server
 * @SuppressWarnings(PHPMD.ShortVariable)
 */
class ServerClient
{

    /** @var string */
    private $id = '';

    /** @var resource|null */
    private $socket = null;

    /** @var string */
    private $host = '';

    /** @var int */
    private $port = 0;

    /** @var array */
    private $attributes = [];

    public function __construct()
    {
        $this->id = spl_object_hash($this);
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param resource|null $socket
     * @return self
     */
    public function setSocket($socket): self
    {
        if (!is_resource($socket)) {
            $socket = null;
        }
        $this->socket = $socket;
        return $this;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function setAttribute(string $key, $value): self
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    /**
     * @todo fix mixed return types!
     * @param string $key
     * @return mixed|null
     */
    public function getAttribute(string $key)
    {
        if (!isset($this->attributes[$key])) {
            return null;
        }
        return $this->attributes[$key];
    }

    /**
     * @todo fix mixed return types!
     * @return resource|null
     */
    public function getSocket()
    {
        return $this->socket;
    }

    /**
     * @param string $host
     * @return self
     */
    public function setHost(string $host): self
    {
        $this->host = $host;
        return $this;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param int $port
     * @return self
     * @throws \InvalidArgumentException
     */
    public function setPort(int $port): self
    {
        if ($port < 0 || $port > 65535) {
            throw new \InvalidArgumentException('The port ' . $port . ' is not allowed.');
        }
        $this->port = $port;
        return $this;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

}
