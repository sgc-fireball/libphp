<?php

namespace HRDNS\Tests\Types;

use HRDNS\Types\IPv4;

class IPv4Test extends \PHPUnit_Framework_TestCase
{

    public function testCalculation()
    {
        $ip = new IPv4('192.168.2.1', 24);
        $this->assertEquals('192.168.2.1', $ip->getIp());
        $this->assertEquals('2002:00C0:00A8:0002:0001::', $ip->getIpv6());
        $this->assertEquals(24, $ip->getCIDR());
        $this->assertEquals('255.255.255.0', $ip->getSubnetmask());
        $this->assertEquals('192.168.2.0', $ip->getNetmask());
        $this->assertEquals('192.168.2.255', $ip->getBroadcast());
        $this->assertEquals('1.2.168.192.in-addr.arpa', $ip->getInArpa());
        $this->assertFalse($ip->isIpInSubnet('192.168.1.128'));
        $this->assertTrue($ip->isIpInSubnet('192.168.2.128'));
        $this->assertFalse($ip->isIpInSubnet('192.168.3.128'));
    }

    public function testSuperNetworking()
    {
        $ip = new IPv4('192.168.2.1', 24);
        $ip->setSubnetmask('255.255.0.0');
        $this->assertEquals('192.168.2.1', $ip->getIp());
        $this->assertEquals('2002:00C0:00A8:0002:0001::', $ip->getIpv6());
        $this->assertEquals(16, $ip->getCIDR());
        $this->assertEquals('255.255.0.0', $ip->getSubnetmask());
        $this->assertEquals('192.168.0.0', $ip->getNetmask());
        $this->assertEquals('192.168.255.255', $ip->getBroadcast());
        $this->assertEquals('1.2.168.192.in-addr.arpa', $ip->getInArpa());
        $this->assertFalse($ip->isIpInSubnet('192.167.1.128'));
        $this->assertTrue($ip->isIpInSubnet('192.168.2.128'));
        $this->assertFalse($ip->isIpInSubnet('192.169.3.128'));
    }

}
