<?php

namespace HRDNS\Socket\Server;

abstract class Server
{

    /** @var integer */
    protected $port = 20;

    /** @var string */
    protected $listen = '0.0.0.0';

    /** @var integer */
    protected $bufferLength = 8192;

    /** @var resource */
    protected $masterSocket = null;

    /** @var bool */
    protected $isTerminated = false;

    /** @var ServerClient[] */
    protected $clients = array ();

    /** @var integer */
    protected $timeoutSeconds = 1;

    /** @var integer */
    protected $timeoutUSeconds = 0;

    /**
     * @param string $listen
     * @return static
     * @throws \InvalidArgumentException
     */
    public function setListen($listen)
    {
        $block = '(\d{1,2}|1\d{1,2}|2(0|1|2|3|4)(\d{1})|25(0|1|2|3|4|5))';
        if (!preg_match('/^((' . $block . '\.){3}' . $block . ')$/', $listen)) {
            throw new \InvalidArgumentException('The listen ip ' . $listen . ' is invalid.');
        }
        $this->listen = $listen;
        return $this;
    }

    /**
     * @param integer $port
     * @return static
     * @throws \InvalidArgumentException
     */
    public function setPort($port)
    {
        $port = (int)$port;
        if ($port < 1 || $port > 65535) {
            throw new \InvalidArgumentException('The port ' . $port . ' is not allowed.');
        }
        $this->port = $port;
        return $this;
    }

    /**
     * @param integer $bufferLength
     * @return static
     * @throws \InvalidArgumentException
     */
    public function setBufferLength($bufferLength)
    {
        $bufferLength = (int)$bufferLength;
        if (($bufferLength % 8) !== 0) {
            throw new \InvalidArgumentException('The buffer length must be divisible by 8.');
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
     * @return boolean
     */
    public function hasTerminated()
    {
        return $this->isTerminated;
    }

    public function __destruct()
    {
        $this->terminated();
        foreach ($this->clients as $client) {
            $this->disconnect($client, false);
        }
        @socket_close($this->masterSocket);
        $this->masterSocket = null;
    }

    /**
     * @param ServerClient $client
     * @return void
     */
    abstract public function onConnect(ServerClient $client);

    /**
     * @param ServerClient $client
     * @param string $buffer
     * @return void
     */
    abstract public function onIncoming(ServerClient $client, $buffer);

    /**
     * @param ServerClient $client
     * @param string $buffer
     * @return void
     */
    abstract public function onOutgoing(ServerClient $client, $buffer);

    /**
     * @param ServerClient $client
     * @param bool $closeByPeer
     * @return void
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    abstract public function onDisconnect(ServerClient $client, $closeByPeer = false);

    /**
     * @param ServerClient $client
     * @return void
     */
    abstract public function onTick(ServerClient $client);

    /**
     * @param ServerClient $client
     * @param bool $closeByPeer
     * @return static
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    abstract public function disconnect(ServerClient $client, $closeByPeer = false);

}
