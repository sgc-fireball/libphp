<?php

namespace HRDNS\Socket\Server;

abstract class UDPServer extends Server
{

    /**
     * @return static
     * @throws \Exception
     */
    public function bind()
    {
        $this->masterSocket = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if ($this->masterSocket === null) {
            $errNo = @socket_last_error($this->masterSocket);
            $errStr = @socket_strerror($errNo);
            throw new \Exception(
                sprintf(
                    'ERROR[%d] udp://%s:%s - %s',
                    $errNo,
                    $this->listen,
                    $this->port,
                    $errStr
                )
            );
        }

        if (@socket_bind($this->masterSocket, $this->listen, $this->port) === false) {
            $errNo = @socket_last_error($this->masterSocket);
            $errStr = @socket_strerror($errNo);
            throw new \Exception(
                sprintf(
                    'ERROR[%d] udp://%s:%s - %s',
                    $errNo,
                    $this->listen,
                    $this->port,
                    $errStr
                )
            );
        }

        return $this;
    }

    /**
     * @param integer $limit
     * @return static
     */
    public function listen($limit = -1)
    {
        while (!$this->isTerminated && ($limit > 0 || $limit == -1)) {
            $limit = $limit == -1 ? -1 : $limit - 1;
            $this->workOnMasterSocket();
            $this->workOnClientSockets();
        }
        return $this;
    }

    /**
     * @param ServerClient $client
     * @param bool $closeByPeer
     * @return static
     */
    public function disconnect(ServerClient $client, $closeByPeer = false)
    {
        $this->onDisconnect($client, $closeByPeer);
        unset($this->clients[$client->getId()]);
        return $this;
    }

    /**
     * @param ServerClient $client
     * @param string $buffer
     * @param integer|null $length
     * @return boolean|integer
     */
    public function send(ServerClient $client, $buffer, $length = null)
    {
        $this->onOutgoing($client, $buffer);
        $length = $length === null ? strlen($buffer) : $length;
        if ($length === 0) {
            return false;
        }
        return @socket_sendto($this->masterSocket, $buffer, $length, 0, $client->getHost(), $client->getPort());
    }

    /**
     * @return static
     */
    protected function workOnMasterSocket()
    {
        if (@socket_recvfrom(
            $this->masterSocket,
            $buffer,
            $this->bufferLength,
            MSG_DONTWAIT,
            $src,
            $spt
        )
        ) {
            $client = $this->getClientByIpAndPort($src, $spt);
            if (!($client instanceof ServerClient)) {
                $client = new ServerClient();
                $client->setHost($src);
                $client->setPort($spt);
                $this->clients[$client->getId()] = $client;
                $this->onConnect($client);
            }
            $client->setAttribute(
                'timeout',
                microtime(true) + $this->timeoutSeconds + ($this->timeoutUSeconds / 1000)
            );
            $this->onIncoming($client, $buffer);
        }
        return $this;
    }

    /**
     * @param resource[] $read
     * @return static
     */
    protected function workOnClientSockets(array $read = array ())
    {
        foreach ($this->clients as $client) {
            if ($this->isTerminated) {
                break;
            }

            /** check timeouts **/
            if ($client->getAttribute('timeout') < microtime(true)) {
                $this->disconnect($client, false);
                continue;
            }

            /** handle tick **/
            $this->onTick($client);
        }

        return $this;
    }

    /**
     * @param string $src
     * @param int $spt
     * @return ServerClient|null
     */
    protected function getClientByIpAndPort($src, $spt)
    {
        foreach ($this->clients as $client) {
            if ($client->getHost() != $src) {
                continue;
            }
            if ($client->getPort() != $spt) {
                continue;
            }
            return $client;
        }
        return null;
    }

}
