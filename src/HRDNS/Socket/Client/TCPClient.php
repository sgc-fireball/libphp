<?php

namespace HRDNS\Socket\Client;

class TCPClient extends Client
{

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

}
