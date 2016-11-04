<?php

namespace Tests\HRDNS\Types;

use HRDNS\Types\IPv6;

class IPv6Test extends \PHPUnit_Framework_TestCase
{

    public function testCalculation()
    {
        $ip = new IPv6('fe80:dead:0015:000a:0bad:1dea:0000:0001', 65);
        $this->assertEquals('fe80:dead:15:a:bad:1dea::1', $ip->getIp());
        $this->assertEquals(65, $ip->getCIDR());
        $this->assertEquals('ffff:ffff:ffff:ffff:8000::', $ip->getSubnetmask());
        $this->assertEquals('fe80:dead:15:a::', $ip->getNetmask());
        $this->assertEquals('fe80:dead:15:a:7fff:ffff:ffff:ffff', $ip->getBroadcast());
        $this->assertEquals(
            '1.0.0.0.0.0.0.0.a.e.d.1.d.a.b.0.a.0.0.0.5.1.0.0.d.a.e.d.0.8.e.f.ip6.arpa',
            $ip->getInArpa()
        );
        $this->assertFalse($ip->isIpInSubnet('fe80:dead:15:9::'));
        $this->assertTrue($ip->isIpInSubnet('fe80:dead:15:a::'));
        $this->assertFalse($ip->isIpInSubnet('fe80:dead:15:b::'));
        $this->assertEquals('338293041328690602920104126360248647681', $ip->getLong());
    }

    public function testSuperNetworking()
    {
        $ip = new IPv6('fe80:dead:0015:000a:0bad:1dea:0000:0001', 65);
        $ip->setSubnetmask('ffff:ffff:ffff:ffff::');
        $this->assertEquals('fe80:dead:15:a:bad:1dea::1', $ip->getIp());
        $this->assertEquals(64, $ip->getCIDR());
        $this->assertEquals('ffff:ffff:ffff:ffff::', $ip->getSubnetmask());
        $this->assertEquals('fe80:dead:15:a::', $ip->getNetmask());
        $this->assertEquals('fe80:dead:15:a:ffff:ffff:ffff:ffff', $ip->getBroadcast());
        $this->assertEquals(
            '1.0.0.0.0.0.0.0.a.e.d.1.d.a.b.0.a.0.0.0.5.1.0.0.d.a.e.d.0.8.e.f.ip6.arpa',
            $ip->getInArpa()
        );
        $this->assertFalse($ip->isIpInSubnet('fe80:dead:15:9::'));
        $this->assertTrue($ip->isIpInSubnet('fe80:dead:15:a::'));
        $this->assertFalse($ip->isIpInSubnet('fe80:dead:15:b::'));
    }

    public function testIpv6TpLongToIpv6()
    {
        $ip = new IPv6('fe80:dead:0015:000a:0bad:1dea:0000:0001');
        $this->assertEquals('fe80:dead:15:a:bad:1dea::1', $ip->getIp());
        $this->assertEquals('338293041328690602920104126360248647681', $ip->getLong());
        $ip->setLong($ip->getLong());
        $this->assertEquals('fe80:dead:15:a:bad:1dea::1', $ip->getIp());
    }

}
