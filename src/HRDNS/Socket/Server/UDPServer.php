<?php

namespace HRDNS\Socket\Server;

abstract class UDPServer extends Server
{

    /**
     * @return self
     * @throws \Exception
     */
    public function bind(): self
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
     * @param int $limit
     * @return self
     */
    public function listen(int $limit = -1): self
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
     * @return self
     * @SuppressWarnings(PHPMD.boolArgumentFlag)
     */
    public function disconnect(ServerClient $client)
    {
        unset($this->clients[$client->getId()]);
        return $this;
    }

    /**
     * @todo fix mixed return types!
     * @param ServerClient $client
     * @param string $buffer
     * @param int|null $length
     * @return int|bool
     */
    public function send(ServerClient $client, string $buffer, int $length = null)
    {
        $this->onOutgoing($client, $buffer);
        $length = $length === null ? strlen($buffer) : $length;
        if ($length === 0) {
            return false;
        }
        return @socket_sendto($this->masterSocket, $buffer, $length, 0, $client->getHost(), $client->getPort());
    }

    /**
     * @return self
     */
    protected function workOnMasterSocket(): self
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
     * @return self
     */
    protected function workOnClientSockets(): self
    {
        foreach ($this->clients as $client) {
            if ($this->isTerminated) {
                break;
            }

            /** check timeouts **/
            if ($client->getAttribute('timeout') < microtime(true)) {
                $this->onDisconnect($client);
                $this->disconnect($client);
                continue;
            }

            /** handle tick **/
            $this->onTick($client);
        }

        return $this;
    }

    /**
     * @todo fix mixed return types!
     * @param string $src
     * @param int $spt
     * @return ServerClient|null
     */
    protected function getClientByIpAndPort(string $src, int $spt)
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
