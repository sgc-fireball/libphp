<?php

namespace HRDNS\Socket\Server;

class Client
{

    /** @var resource|null */
    private $socket = null;

    /** @var string */
    private $id = null;

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
     * @return resource|null
     */
    public function getSocket()
    {
        return $this->socket;
    }

}
