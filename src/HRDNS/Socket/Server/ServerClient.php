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
    private $host = null;

    /** @var integer */
    private $port = null;

    /** @var array */
    private $attributes = array ();

    public function __construct()
    {
        $this->id = spl_object_hash($this);
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param resource|null $socket
     * @return static
     */
    public function setSocket($socket)
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
     * @return static
     */
    public function setAttribute($key, $value)
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        if (!isset($this->attributes[$key])) {
            return null;
        }
        return $this->attributes[$key];
    }

    /**
     * @return resource|null
     */
    public function getSocket()
    {
        return $this->socket;
    }

    /**
     * @param string $host
     * @return static
     */
    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param integer $port
     * @return static
     */
    public function setPort($port)
    {
        $this->port = (int)$port;
        return $this;
    }

    /**
     * @return integer|null
     */
    public function getPort()
    {
        return $this->port;
    }

}
