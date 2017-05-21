<?php

namespace HRDNS\Socket\Client;

class UDPClient extends Client
{

    protected $allowBroadcast = false;

    /**
     * @return self
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
     * @todo fix mixed return types!
     * @param integer|null $length
     * @return string|boolean
     */
    public function read(int $length = null)
    {
        $length = $length === null ? $this->bufferLength : $length;
        if (!$length) {
            return false;
        }
        if (!@socket_recvfrom($this->socket, $buffer, $length, MSG_DONTWAIT, $src, $spt)) {
            return false;
        }
        if (!$this->allowBroadcast && ($this->host !== $src || $this->port !== $spt)) {
            return false;
        }
        return $buffer;
    }

    /**
     * @todo fix mixed return types!
     * @param string $buffer
     * @param integer|null $length
     * @return integer|boolean
     */
    public function write(string $buffer, int $length = null)
    {
        $length = $length === null ? strlen($buffer) : $length;
        return @socket_sendto($this->socket, $buffer, $length, 0, $this->host, $this->port);
    }

    /**
     * @param boolean $allowBroadcast
     * @return $this
     */
    public function setAllowBrowscast(bool $allowBroadcast)
    {
        $this->allowBroadcast = (bool)$allowBroadcast;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getAllowBrowscast(): bool
    {
        return $this->allowBroadcast;
    }

}
