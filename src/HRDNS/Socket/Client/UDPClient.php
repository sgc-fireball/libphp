<?php

namespace HRDNS\Socket\Client;

class UDPClient
{

    /** @var string */
    private $host = null;

    /** @var integer */
    private $port = 20;

    /** @var resource|null */
    private $socket = null;

    /** @var integer */
    private $bufferLength = 8192;

    /** @var integer */
    private $timeoutSeconds = 1;

    /** @var integer */
    private $timeoutUSeconds = 0;

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
     * @throws \Exception
     */
    public function connect()
    {
        if (is_resource($this->socket)) {
            return $this;
        }
        $this->socket = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if ($this->socket === false) {
            $errNo = @socket_last_error($this->socket);
            $errStr = @socket_strerror($errNo);
            throw new \Exception(
                sprintf(
                    'ERROR[%d] create udp://%s:%s - %s',
                    $errNo,
                    $this->host,
                    $this->port,
                    $errStr
                )
            );
        }
        return $this;
    }

    /**
     * @param integer|null $length
     * @return string|boolean
     */
    public function read($length = null)
    {
        $length = $length === null ? $this->bufferLength : $length;
        if (!$length) {
            return false;
        }
        if (!@socket_recvfrom($this->socket, $buffer, $length, MSG_DONTWAIT, $src, $spt)) {
            return false;
        }
        if ($this->host !== $src || $this->port !== $spt) {
            return false;
        }
        return $buffer;
    }

    /**
     * @param string $buffer
     * @param integer|null $length
     * @return boolean|integer
     */
    public function write($buffer, $length = null)
    {
        $length = $length === null ? strlen($buffer) : $length;
        return @socket_sendto($this->socket, $buffer, $length, 0, $this->host, $this->port);
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
