<?php

namespace HRDNS\Socket\Server;

abstract class TCPServer
{

    /** @var integer */
    private $port = 20;

    /** @var string */
    private $listen = '0.0.0.0';

    /** @var integer */
    private $bufferLength = 8192;

    /** @var resource */
    private $masterSocket = null;

    /** @var integer */
    private $maxClients = 20;

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
        $this->masterSocket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        @socket_set_option($this->masterSocket, SOL_SOCKET, SO_REUSEADDR, 1);
        @socket_set_nonblock($this->masterSocket);

        if ($this->masterSocket === null) {
            $errNo = @socket_last_error($this->masterSocket);
            $errStr = @socket_strerror($errNo);
            throw new \Exception(
                sprintf(
                    'ERROR[%d] tcp://%s:%s - %s',
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
                    'ERROR[%d] tcp://%s:%s - %s',
                    $errNo,
                    $this->listen,
                    $this->port,
                    $errStr
                )
            );
        }

        if (@socket_listen($this->masterSocket, $this->maxClients) === false) {
            $errNo = @socket_last_error($this->masterSocket);
            $errStr = @socket_strerror($errNo);
            throw new \Exception(
                sprintf(
                    'ERROR[%d] tcp://%s:%s - %s',
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
            $this->workOnMasterSocket();

            $limit = $limit == -1 ? -1 : $limit - 1;
            $read = $write = $except = array ();
            foreach ($this->clients as $client) {
                $read[] = $client->getSocket();
            }

            @socket_select($read, $write, $except, $this->timeoutSeconds, $this->timeoutUSeconds);
            $this->workOnClientSockets($read);

            foreach ($this->clients as $client) {
                if ($this->isTerminated) {
                    break;
                }
                $this->onTick($client);
            }
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
        if ($client->getSocket() === null) {
            return $this;
        }
        $this->onDisconnect($client, $closeByPeer);
        @socket_close($client->getSocket());
        $client->setSocket(null);
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
        if ($client->getSocket() === null || empty($buffer)) {
            return false;
        }
        $this->onOutgoing($client, $buffer);
        $length = $length === null ? strlen($buffer) : $length;
        if ($length === 0) {
            return false;
        }
        return @socket_write($client->getSocket(), $buffer, $length);
    }

    /**
     * @return static
     */
    private function workOnMasterSocket()
    {
        $socket = @socket_accept($this->masterSocket);
        if (is_resource($socket)) {
            @socket_getpeername($socket, $src, $spt);
            $client = new Client();
            $client->setSocket($socket);
            $client->setHost($src);
            $client->setPort($spt);
            $this->clients[$client->getId()] = $client;
            $this->onConnect($client);
        }
        return $this;
    }

    /**
     * @param resource[] $read
     * @return static
     */
    private function workOnClientSockets(array $read = array ())
    {
        /** @var resource $socket */
        foreach ($read as $socket) {
            if ($this->isTerminated) {
                break;
            }

            $client = $this->getClientBySocket($socket);
            if (!$client) {
                @socket_close($socket);
                continue;
            }

            $this->workOnClientSocket($client);
        }

        return $this;
    }

    /**
     * @param Client $client
     * @return static
     */
    private function workOnClientSocket(Client $client)
    {
        $bytes = @socket_recv($client->getSocket(), $buffer, $this->bufferLength, 0);
        if ($bytes !== 0 && $bytes !== false) {
            $this->onIncoming($client, $buffer);
            return $this;
        }
        $this->disconnect($client, true);
        return $this;
    }

    /**
     * @param resource $socket
     * @return Client|null
     */
    private function getClientBySocket($socket)
    {
        if (!is_resource($socket)) {
            return null;
        }
        foreach ($this->clients as $client) {
            if ($client->getSocket() == $socket) {
                return $client;
            }
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
