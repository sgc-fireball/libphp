<?php

namespace HRDNS\Socket\Server;

abstract class TCPServer extends Server
{

    /** @var integer */
    protected $maxClients = 20;

    /**
     * @param int $maxClients
     * @return self
     */
    public function setMaxClients($maxClients)
    {
        $this->maxClients = max(1, (int)$maxClients);
        return $this;
    }

    /**
     * @return self
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
     * @return self
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
     * @param ServerClient $client
     * @param bool $closeByPeer
     * @return self
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function disconnect(ServerClient $client, $closeByPeer = false)
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
     * @param ServerClient $client
     * @param string $buffer
     * @param integer|null $length
     * @return boolean|integer
     */
    public function send(ServerClient $client, $buffer, $length = null)
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
     * @return self
     */
    protected function workOnMasterSocket()
    {
        $socket = @socket_accept($this->masterSocket);
        if (is_resource($socket)) {
            @socket_getpeername($socket, $src, $spt);
            $client = new ServerClient();
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
     * @return self
     */
    protected function workOnClientSockets(array $read = array ())
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
     * @param ServerClient $client
     * @return self
     */
    protected function workOnClientSocket(ServerClient $client)
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
     * @return ServerClient|null
     */
    protected function getClientBySocket($socket)
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

}
