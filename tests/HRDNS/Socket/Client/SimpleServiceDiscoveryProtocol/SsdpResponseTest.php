<?php declare(strict_types=1);

namespace HRDNS\Tests\Socket\Client\SimpleServiceDiscoveryProtocol;

use HRDNS\Socket\Client\SimpleServiceDiscoveryProtocol\SsdpResponse;

class SsdpResponseTest extends \PHPUnit\Framework\TestCase
{

    /** @var SsdpResponse */
    private $ssdpResponseStruct;

    /** @var SsdpResponse */
    private $ssdpResponseString;

    protected function setUp(): void
    {
        $this->ssdpResponseStruct = new SsdpResponse([
            'location' => 'http://127.0.0.1/desc.xml',
            'server' => 'test upnp server',
            'otherfields' => 'otherfields'
        ]);
        $this->ssdpResponseString = (new SsdpResponse())->setFromString(
            "HTTP/1.1 200 OK\n" .
            "Location: http://127.0.0.1/desc.xml\n" .
            "Server: test upnp server\n" .
            "OtherFields: otherfields"
        );
    }

    public function testStructLocation()
    {
        $this->assertEquals('http://127.0.0.1/desc.xml', $this->ssdpResponseStruct->getLocation());
    }

    public function testStructServer()
    {
        $this->assertEquals('test upnp server', $this->ssdpResponseStruct->getServer());
    }

    public function testStructOtherFields()
    {
        $this->assertEquals('otherfields', $this->ssdpResponseStruct->otherfields);
    }

    public function testStringLocation()
    {
        $this->assertEquals('http://127.0.0.1/desc.xml', $this->ssdpResponseString->getLocation());
    }

    public function testStringServer()
    {
        $this->assertEquals('test upnp server', $this->ssdpResponseString->getServer());
    }

    public function testStringOtherFields()
    {
        $this->assertEquals('otherfields', $this->ssdpResponseString->otherfields);
    }

}
