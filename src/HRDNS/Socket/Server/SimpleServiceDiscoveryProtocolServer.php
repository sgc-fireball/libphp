<?php

namespace HRDNS\Socket\Server;

use HRDNS\Core\EventHandler;
use HRDNS\Socket\Client\SimpleServiceDiscoveryProtocol\SsdpResponse;
use HRDNS\Socket\Client\SimpleServiceDiscoveryProtocol\EventDiscover;

class SimpleServiceDiscoveryProtocolServer extends UDPServer
{

    /** @var EventHandler */
    private static $eventHandler = null;

    /** @var array */
    private $services = [];

    public function __construct()
    {
        self::$eventHandler = self::$eventHandler ?: EventHandler::get();

        $this->setTimeout(1, 0);
        $this->setPort(1900);
        $this->setListen('0.0.0.0');
    }

    /**
     * @param array $service
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addService(array $service)
    {
        if (!isset($service['location'])) {
            throw new \InvalidArgumentException('Missing service parameter: location');
        }
        if (!isset($service['server'])) {
            throw new \InvalidArgumentException('Missing service parameter: server');
        }
        $service['ttl'] = isset($service['ttl']) ? $service['ttl'] : 1800;
        $service['usn'] = isset($service['usn']) ? $service['usn'] : 'uuid:' . sha1(json_encode($service));
        $this->services[] = $service;
        return $this;
    }

    /**
     * @param ServerClient $client
     * @param string $buffer
     * @return void
     */
    public function onIncoming(ServerClient $client, string $buffer)
    {
        if (preg_match('/^NOTIFY/', $buffer)) {
            $response = new SsdpResponse();
            $response->setFromString($buffer);
            if ($response->getHttpCode() !== 200) {
                return;
            }
            self::$eventHandler->fireEvent(
                EventDiscover::EVENT_NAME,
                new EventDiscover($response)
            );
            return;
        }

        if (preg_match('/^(OPTIONS|M-SEARCH) /', $buffer)) {
            foreach ($this->services as $service) {
                $buffer = sprintf(
                    "HTTP/1.1 200 OK\n" .
                    "LOCATION: %s\n" .
                    "SERVER: %s\n" .
                    "CACHE-CONTROL: max-age=%d\n" .
                    "EXT:\n" .
                    "ST: %s\n" .
                    "USN: %s\n",
                    $service['location'],
                    $service['server'],
                    $service['ttl'],
                    $service['usn'],
                    $service['usn']
                );
                $this->send($client, $buffer);
            }
        }
    }

    /**
     * @param string $name
     * @param callable $callable
     * @param integer $priority
     * @return boolean
     */
    public function addEvent(string $name, callable $callable, int $priority = 0): bool
    {
        return self::$eventHandler->addEvent($name, $callable, $priority);
    }

    /**
     * @param ServerClient $client
     * @return void
     */
    public function onConnect(ServerClient $client)
    {
    }

    /**
     * @param ServerClient $client
     * @param string $buffer
     * @return void
     */
    public function onOutgoing(ServerClient $client, string $buffer)
    {
    }

    /**
     * @param ServerClient $client
     * @return void
     * @SuppressWarnings(PHPMD.boolArgumentFlag)
     */
    public function onDisconnect(ServerClient $client)
    {
    }

    /**
     * @param ServerClient $client
     * @return void
     */
    public function onTick(ServerClient $client)
    {
    }

}
