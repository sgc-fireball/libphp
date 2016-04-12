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
     * @return self
     */
    public function setHost(string $host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * @param integer $port
     * @return self
     * @throws \Exception
     */
    public function setPort(int $port)
    {
        if ($port < 1 || $port > 65535) {
            throw new \Exception('The port ' . $port . ' is not allowed.');
        }
        $this->port = $port;
        return $this;
    }

    /**
     * @param integer $timeoutSeconds
     * @param integer $timeoutUSeconds
     * @return self
     */
    public function setTimeout(int $timeoutSeconds, int $timeoutUSeconds)
    {
        $this->timeoutSeconds = $timeoutSeconds;
        $this->timeoutUSeconds = $timeoutUSeconds;
        return $this;
    }

    /**
     * @param integer $bufferLength
     * @return self
     * @throws \Exception
     */
    public function setBufferLength(int $bufferLength)
    {
        $bufferLength = (int)$bufferLength;
        if (($bufferLength % 8) !== 0) {
            throw new \Exception('The buffer length must be divisible by 8.');
        }
        $this->bufferLength = $bufferLength;
        return $this;
    }

    /**
     * @return self
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
