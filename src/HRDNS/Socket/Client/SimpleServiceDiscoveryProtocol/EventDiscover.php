<?php

namespace HRDNS\Socket\Client\SimpleServiceDiscoveryProtocol;

use HRDNS\Core\Event;

class EventDiscover extends Event
{

    const EVENT_NAME = 'SimpleServiceDiscoveryProtocolClient:discover';

    /** @var SsdpResponse */
    private $service = [];

    /**
     * @param SsdpResponse $service
     */
    public function __construct(SsdpResponse $service)
    {
        $this->service = $service;
    }

    /**
     * @return SsdpResponse
     */
    public function getSsdpResponse()
    {
        return $this->service;
    }

}
