<?php declare(strict_types=1);

namespace HRDNS\Socket\Server;

abstract class Server
{

    /** @var int */
    protected $port = 20;

    /** @var string */
    protected $listen = '0.0.0.0';

    /** @var int */
    protected $bufferLength = 8192;

    /** @var resource|null */
    protected $masterSocket = null;

    /** @var bool */
    protected $isTerminated = false;

    /** @var ServerClient[] */
    protected $clients = [];

    /** @var int */
    protected $timeoutSeconds = 1;

    /** @var int */
    protected $timeoutUSeconds = 0;

    /**
     * @param string $listen
     * @return self
     * @throws \InvalidArgumentException
     */
    public function setListen(string $listen)
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
     * @return self
     * @throws \InvalidArgumentException
     */
    public function setPort(int $port)
    {
        if ($port < 1 || $port > 65535) {
            throw new \InvalidArgumentException('The port ' . $port . ' is not allowed.');
        }
        $this->port = $port;

        return $this;
    }

    /**
     * @param integer $bufferLength
     * @return self
     * @throws \InvalidArgumentException
     */
    public function setBufferLength(int $bufferLength)
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
     * @return self
     */
    public function setTimeout(int $timeoutSeconds, int $timeoutUSeconds)
    {
        $this->timeoutSeconds = $timeoutSeconds;
        $this->timeoutUSeconds = $timeoutUSeconds;

        return $this;
    }

    /**
     * @return self
     */
    public function terminated()
    {
        $this->isTerminated = true;

        return $this;
    }

    /**
     * @return boolean
     */
    public function hasTerminated(): bool
    {
        return $this->isTerminated;
    }

    public function __destruct()
    {
        $this->terminated();
        foreach ($this->clients as $client) {
            $this->onDisconnect($client);
            $this->disconnect($client);
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
    abstract public function onIncoming(ServerClient $client, string $buffer);

    /**
     * @param ServerClient $client
     * @param string $buffer
     * @return void
     */
    abstract public function onOutgoing(ServerClient $client, string $buffer);

    /**
     * @param ServerClient $client
     * @return void
     * @SuppressWarnings(PHPMD.boolArgumentFlag)
     */
    abstract public function onDisconnect(ServerClient $client);

    /**
     * @param ServerClient $client
     * @return void
     */
    abstract public function onTick(ServerClient $client);

    /**
     * @param ServerClient $client
     * @return self
     * @SuppressWarnings(PHPMD.boolArgumentFlag)
     */
    abstract public function disconnect(ServerClient $client);

}
