<?php

namespace HRDNS\Socket\Client;

use HRDNS\Core\EventHandler;
use HRDNS\Socket\Server\SimpleServiceDiscoveryProtocolServer;
use HRDNS\Socket\Client\SimpleServiceDiscoveryProtocol\SsdpResponse;
use HRDNS\Socket\Client\SimpleServiceDiscoveryProtocol\EventDiscover;

/**
 * @see http://www.upnp.org/specs/arch/UPnP-arch-DeviceArchitecture-v1.0-20080424.pdf
 */
class SimpleServiceDiscoveryProtocolClient
{

    /** @var string */
    private $host = '239.255.255.250';

    /** @var int */
    private $port = 1900;

    /** @var EventHandler */
    private static $eventHandler = null;

    /** @var null|SimpleServiceDiscoveryProtocolServer */
    private $server = null;

    public function __construct()
    {
        self::$eventHandler = self::$eventHandler ?: EventHandler::get();
    }

    /**
     * @param SimpleServiceDiscoveryProtocolServer $server
     * @return $this
     */
    public function setServer(SimpleServiceDiscoveryProtocolServer $server)
    {
        $this->server = $server;
        return $this;
    }

    /**
     * @param integer $discoverTime
     * @return $this
     */
    public function discover(int $discoverTime = 10)
    {
        $client = new UDPClient();
        $client->setHost($this->host);
        $client->setPort($this->port);
        $client->setTimeout($discoverTime, 0);
        $client->setAllowBrowscast(true);
        $client->connect();

        $client->write($this->buildPacket('OPTIONS', max(1, $discoverTime / 2)));
        $client->write($this->buildPacket('M-SEARCH', max(1, $discoverTime / 2)));

        $endTime = time() + $discoverTime;
        while (time() < $endTime) {
            if ($this->server) {
                $this->server->listen(1);
            }
            /** @var string $msg */
            if (!$msg = $client->read()) {
                usleep(0.01);
                continue;
            }

            self::$eventHandler->fireEvent(
                EventDiscover::EVENT_NAME,
                new EventDiscover(
                    (new SsdpResponse())
                        ->setFromString($msg)
                )
            );
        }
        $client->disconnect();
        return $this;
    }

    /**
     * @param string $type
     * @param integer $discoverTime
     * @return string
     */
    private function buildPacket(string $type = 'NOTIFY', int $discoverTime = 10)
    {
        $lines = [];
        $lines[] = sprintf('%s * HTTP/1.1', $type);
        $lines[] = sprintf('HOST: %s:%d', $this->host, $this->port);
        $lines[] = 'MAN: "ssdp:discover"';
        $lines[] = 'NTS: ssdp:alive';
        $lines[] = 'ST: ssdp:all';
        $lines[] = sprintf('MX: %d', $discoverTime);
        $lines[] = sprintf('USER-AGENT: php-%s/%s UPnP/1.0 HRDNS-Client/1.0', php_sapi_name(), phpversion());
        $lines[] = '';
        return implode("\n", $lines);
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

}
