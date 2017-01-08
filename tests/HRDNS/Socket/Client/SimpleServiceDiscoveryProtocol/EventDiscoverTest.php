<?php

namespace HRDNS\Tests\Socket\Client\SimpleServiceDiscoveryProtocol;

use HRDNS\Socket\Client\SimpleServiceDiscoveryProtocol\SsdpResponse;
use HRDNS\Socket\Client\SimpleServiceDiscoveryProtocol\EventDiscover;

class EventDiscoverTest extends \PHPUnit_Framework_TestCase
{

    /** @var EventDiscover */
    private $event;

    public function setUp()
    {
        $this->event = new EventDiscover(
            new SsdpResponse(
                [
                    'location' => 'http://127.0.0.1/desc.xml',
                    'server' => 'test upnp server',
                    'otherfields' => 'otherfields'
                ]
            )
        );
    }

    public function testEventDiscoverLocation()
    {
        $this->assertEquals('http://127.0.0.1/desc.xml',$this->event->getSsdpResponse()->getLocation());
    }

    public function testEventDiscoverServer()
    {
        $this->assertEquals('test upnp server',$this->event->getSsdpResponse()->getServer());
    }

    public function testEventDiscoverOtherFields()
    {
        $this->assertEquals('otherfields',$this->event->getSsdpResponse()->otherfields);
    }

}
