<?php

namespace HRDNS\Socket\Client;

use HRDNS\Socket\Client\SimpleServiceDiscoveryProtocol\SsdpResponse;
use HRDNS\Socket\Client\SimpleServiceDiscoveryProtocol\EventDiscover;
use HRDNS\Core\EventHandler;

/**
 * @see http://www.upnp.org/specs/arch/UPnP-arch-DeviceArchitecture-v1.0-20080424.pdf
 */
class SimpleServiceDiscoveryProtocolClient
{

    /** @var array */
    private $services = [];

    /** @var string */
    private $host = '239.255.255.250';

    /** @var int */
    private $port = 1900;

    /** @var EventHandler */
    private static $eventHandler = null;

    /**
     * @param integer $discoverTime
     */
    public function __construct()
    {
        self::$eventHandler = self::$eventHandler ?: EventHandler::get();
    }

    /**
     * @param integer $discoverTime
     * @return SsdpResponse[]
     */
    public function discover(int $discoverTime = 10): array
    {
        $client = new UDPClient();
        $client->setHost($this->host);
        $client->setPort($this->port);
        $client->setTimeout($discoverTime, 0);
        $client->setAllowBrowscast(true);
        $client->connect();

        // https://tools.ietf.org/html/draft-cai-ssdp-v1-01
        $client->write($this->buildPacket('OPTIONS', $discoverTime));
        // free nature
        $client->write($this->buildPacket('NOTIFY', $discoverTime));
        $client->write($this->buildPacket('M-SEARCH', $discoverTime));

        $endTime = time() + $discoverTime;
        while (time() < $endTime) {
            /** @var string $msg */
            if (!$msg = $client->read()) {
                usleep(0.01);
                continue;
            }

            $answer = new SsdpResponse();
            $answer->setFromString($msg);
            if (isset($this->services[$answer->getLocation()])) {
                continue;
            }
            $event = new EventDiscover($answer);
            self::$eventHandler->fireEvent(
                EventDiscover::EVENT_NAME,
                $event
            );
            $this->services[$answer->getLocation()] = $answer;
        }
        $client->disconnect();
        return $this->services;
    }

    /**
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

    public function addEvent(string $name, callable $callable, int $priority = 0): bool
    {
        return self::$eventHandler->addEvent($name, $callable, $priority);
    }

}
