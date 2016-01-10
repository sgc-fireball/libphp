<?php

namespace src\HRDNS\Socket\Client;

class TCPClient
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
     */
    public function setPort($port)
    {
        $port = (int)$port;
        $port = 0 < $port && $port < 65535 ? $port : $this->port;
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
     */
    public function setBufferLength($bufferLength)
    {
        $bufferLength = (int)$bufferLength;
        $bufferLength = ($bufferLength % 8) === 0 ? $bufferLength : $this->bufferLength;
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
        $this->socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($this->socket === null) {
            $errNo = @socket_last_error($this->socket);
            $errStr = @socket_strerror($errNo);
            throw new \Exception(
                sprintf(
                    'ERROR[%d] tcp://%s:%s - %s',
                    $errNo,
                    $this->host,
                    $this->port,
                    $errStr
                )
            );
        }

        @socket_set_option(
            $this->socket,
            SOL_SOCKET,
            SO_RCVTIMEO,
            array(
                'sec' => $this->timeoutSeconds,
                'usec' => $this->timeoutUSeconds
            )
        );
        @socket_set_option(
            $this->socket,
            SOL_SOCKET,
            SO_SNDTIMEO,
            array(
                'sec' => $this->timeoutSeconds,
                'usec' => $this->timeoutUSeconds
            )
        );

        @socket_set_nonblock($this->socket);
        if (!@socket_connect($this->socket, $this->host, $this->port)) {
            $errNo = @socket_last_error($this->socket);
            $errStr = @socket_strerror($errNo);
            throw new \Exception(
                sprintf(
                    'ERROR[%d] tcp://%s:%s - %s',
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
     * @return boolean
     */
    public function read($length = null)
    {
        $length = $length === null ? $this->bufferLength : $length;
        if (!is_resource($this->socket) || !$length) {
            return false;
        }
        return @socket_read($this->socket, $length);
    }

    /**
     * @param string $buffer
     * @param integer|null $length
     * @return boolean|integer
     */
    public function write($buffer, $length = null)
    {
        if (!is_resource($this->socket) || empty($buffer)) {
            return false;
        }
        $length = $length === null ? strlen($buffer) : $length;
        return @socket_write($this->socket, $buffer, $length);
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
