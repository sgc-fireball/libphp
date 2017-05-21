<?php

namespace HRDNS\Socket\Server;

abstract class UDPServer extends Server
{

    /** @var String[] */
    private $acceptedMulticastAddresses = [];

    /**
     * @param string $ipAddr
     * @return UDPServer
     * @throws \InvalidArgumentException
     */
    public function addAllowedMulticastAddress(string $ipAddr)
    {
        if (!filter_var($ipAddr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            throw new \InvalidArgumentException('Parameter $ipAddr is invalid.');
        }
        $this->acceptedMulticastAddresses[] = $ipAddr;
        return $this;
    }

    /**
     * @return self
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

        foreach ($this->acceptedMulticastAddresses as $ipAddr) {
            socket_set_option(
                $this->masterSocket,
                IPPROTO_IP,
                MCAST_JOIN_GROUP,
                ['group' => $ipAddr, 'interface' => 0]
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
     * @return self
     */
    public function listen(int $limit = -1)
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
     * @param integer|null $length
     * @return integer|boolean
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
     * @return self
     */
    protected function workOnClientSockets()
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
     * @param integer $spt
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
