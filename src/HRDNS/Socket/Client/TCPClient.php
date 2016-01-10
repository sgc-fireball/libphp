<?php

namespace HRDNS\Socket\Client;

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
        $this->socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($this->socket === false) {
            $errNo = @socket_last_error($this->socket);
            $errStr = @socket_strerror($errNo);
            throw new \Exception(
                sprintf(
                    'ERROR[%d] create tcp://%s:%s - %s',
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
            array (
                'sec' => $this->timeoutSeconds,
                'usec' => $this->timeoutUSeconds
            )
        );
        @socket_set_option(
            $this->socket,
            SOL_SOCKET,
            SO_SNDTIMEO,
            array (
                'sec' => $this->timeoutSeconds,
                'usec' => $this->timeoutUSeconds
            )
        );

        if (@socket_connect($this->socket, $this->host, $this->port) === false) {
            $errNo = @socket_last_error($this->socket);
            $errStr = @socket_strerror($errNo);
            throw new \Exception(
                sprintf(
                    'ERROR[%d] connect tcp://%s:%s - %s',
                    $errNo,
                    $this->host,
                    $this->port,
                    $errStr
                )
            );
        }
        socket_set_nonblock($this->socket);

        return $this;
    }

    /**
     * @param integer|null $length
     * @return string|boolean
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
