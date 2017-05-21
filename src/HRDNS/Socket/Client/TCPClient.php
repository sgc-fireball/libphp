<?php

namespace HRDNS\Socket\Client;

class TCPClient extends Client
{

    /** @var bool */
    private $blocking = false;

    /**
     * @param bool $blocking
     * @return TCPClient
     */
    public function setBlockingMode(bool $blocking)
    {
        $this->blocking = $blocking;

        return $this;
    }

    /**
     * @return self
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
            throw new \Exception(sprintf('ERROR[%d] create tcp://%s:%s - %s', $errNo, $this->host, $this->port,
                $errStr));
        }

        @socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, [
            'sec' => $this->timeoutSeconds,
            'usec' => $this->timeoutUSeconds
        ]);
        @socket_set_option($this->socket, SOL_SOCKET, SO_SNDTIMEO, [
            'sec' => $this->timeoutSeconds,
            'usec' => $this->timeoutUSeconds
        ]);

        if (@socket_connect($this->socket, $this->host, $this->port) === false) {
            $errNo = @socket_last_error($this->socket);
            $errStr = @socket_strerror($errNo);
            throw new \Exception(sprintf('ERROR[%d] connect tcp://%s:%s - %s', $errNo, $this->host, $this->port,
                $errStr));
        }
        if (!$this->blocking) {
            socket_set_nonblock($this->socket);
        }

        return $this;
    }

    /**
     * @todo fix mixed return types!
     * @param integer|null $length
     * @return string|boolean
     */
    public function read(int $length = null, $type = null)
    {
        $type = $type === null ? PHP_NORMAL_READ : $type;
        $length = $length === null ? $this->bufferLength : $length;
        if (!is_resource($this->socket) || !$length) {
            return false;
        }

        return @socket_read($this->socket, $length, $type);
    }

    /**
     * @param string $buffer
     * @param integer|null $length
     * @return integer|boolean
     */
    public function write(string $buffer, int $length = null)
    {
        if (!is_resource($this->socket) || empty($buffer)) {
            return false;
        }
        $length = $length === null ? strlen($buffer) : $length;

        return @socket_write($this->socket, $buffer, $length);
    }

}
