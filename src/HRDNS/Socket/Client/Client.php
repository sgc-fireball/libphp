<?php declare(strict_types=1);

namespace HRDNS\Socket\Client;

abstract class Client
{

    /** @var string */
    protected $host = '';

    /** @var int */
    protected $port = 0;

    /** @var resource|null */
    protected $socket = null;

    /** @var int */
    protected $bufferLength = 8192;

    /** @var int */
    protected $timeoutSeconds = 1;

    /** @var int */
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
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @param integer $port
     * @return self
     * @throws \Exception
     */
    public function setPort(int $port)
    {
        if ($port < 0 || $port > 65535) {
            throw new \Exception('The port ' . $port . ' is not allowed.');
        }
        $this->port = $port;
        return $this;
    }

    /**
     * @return integer
     */
    public function getPort(): int
    {
        return $this->port;
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
        if (is_resource($this->socket)) {
            @socket_close($this->socket);
        }
        $this->socket = null;
        return $this;
    }

    public function __destruct()
    {
        $this->disconnect();
    }

}
