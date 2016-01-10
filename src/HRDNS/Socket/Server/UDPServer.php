<?php

namespace HRDNS\Socket\Server;

abstract class UDPServer
{

    /** @var integer */
    private $port = 20;

    /** @var string */
    private $listen = '0.0.0.0';

    /** @var integer */
    private $bufferLength = 8192;

    /** @var resource */
    private $masterSocket = null;

    /** @var bool */
    private $isTerminated = false;

    /** @var Client[] */
    private $clients = array ();

    /** @var integer */
    private $timeoutSeconds = 1;

    /** @var integer */
    private $timeoutUSeconds = 0;

    /**
     * @param string $listen
     * @return static
     */
    public function setListen($listen)
    {
        $this->listen = $listen;
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
     * @return static
     */
    public function terminated()
    {
        $this->isTerminated = true;
        return $this;
    }

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
     * @return boolean
     */
    public function hasTerminated()
    {
        return $this->isTerminated;
    }

    /**
     * @param Client $client
     * @param bool $closeByPeer
     * @return static
     */
    public function disconnect(Client $client, $closeByPeer = false)
    {
        $this->onDisconnect($client, $closeByPeer);
        unset($this->clients[$client->getId()]);
        return $this;
    }

    /**
     * @param Client $client
     * @param string $buffer
     * @param integer|null $length
     * @return boolean|integer
     */
    public function send(Client $client, $buffer, $length = null)
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
    private function workOnMasterSocket()
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
            if (!($client instanceof Client)) {
                $client = new Client();
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
    private function workOnClientSockets(array $read = array ())
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
     * @return Client|null
     */
    private function getClientByIpAndPort($src, $spt)
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

    /**
     * @param Client $client
     * @return void
     */
    abstract public function onConnect(Client $client);

    /**
     * @param Client $client
     * @param string $buffer
     * @return void
     */
    abstract public function onIncoming(Client $client, $buffer);

    /**
     * @param Client $client
     * @param string $buffer
     * @return void
     */
    abstract public function onOutgoing(Client $client, $buffer);

    /**
     * @param Client $client
     * @param bool $closeByPeer
     * @return void
     */
    abstract public function onDisconnect(Client $client, $closeByPeer = false);

    /**
     * @param Client $client
     * @return void
     */
    abstract public function onTick(Client $client);

    public function __destruct()
    {
        $this->terminated();
        foreach ($this->clients as $client) {
            $this->disconnect($client, false);
        }
        @socket_close($this->masterSocket);
        $this->masterSocket = null;
    }

}
