<?php

namespace HRDNS\Socket\Client;

abstract class Client
{

    /** @var string */
    protected $host = null;

    /** @var integer */
    protected $port = 20;

    /** @var resource|null */
    protected $socket = null;

    /** @var integer */
    protected $bufferLength = 8192;

    /** @var integer */
    protected $timeoutSeconds = 1;

    /** @var integer */
    protected $timeoutUSeconds = 0;

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
     * @param integer $port
     * @return static
     * @throws \Exception
     */
    public function setPort($port)
    {
        $port = (int)$port;
        if ($port < 1 || $port > 65535) {
            throw new \Exception('The port ' . $port . ' is not allowed.');
        }
        $this->port = $port;
        return $this;
    }

    /**
     * @param integer $timeoutSeconds
     * @param integer $timeoutUSeconds
     * @return static
     */
    public function setTimeout($timeoutSeconds, $timeoutUSeconds)
    {
        $this->timeoutSeconds = (int)$timeoutSeconds;
        $this->timeoutUSeconds = (int)$timeoutUSeconds;
        return $this;
    }

    /**
     * @param integer $bufferLength
     * @return static
     * @throws \Exception
     */
    public function setBufferLength($bufferLength)
    {
        $bufferLength = (int)$bufferLength;
        if (($bufferLength % 8) !== 0) {
            throw new \Exception('The buffer length must be divisible by 8.');
        }
        $this->bufferLength = $bufferLength;
        return $this;
    }

    /**
     * @return static
     */
    public function disconnect()
    {
        @socket_close($this->socket);
        $this->socket = null;
        return $this;
    }

    public function __destruct()
    {
        $this->disconnect();
    }

}
