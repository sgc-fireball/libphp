--TEST--
Testing HRDNS\Types\IPv6 - ipv6 netmask check
--FILE--
<?php
$basePath = preg_replace('/\/tests\/.*/', '', realpath(__DIR__));
require_once($basePath . '/tests/bootstrap.php');

use \HRDNS\Types\IPv6;

$ip = new IPv6('fe80:dead:0015:000a:0bad:1dea:0000:0001', 65);
$ip->setSubnetmask('ffff:ffff:ffff:ffff::');

printf("IPv6: %s\n", $ip->getIp());
printf("CIDR: %s\n", $ip->getCIDR());
printf("Subnetmask: %s\n", $ip->getSubnetmask());
printf("Netmask: %s\n", $ip->getNetmask());
printf("Broadcast: %s\n", $ip->getBroadcast());
printf("ARPA: %s\n", $ip->getInArpa());
printf("fe80:dead:15:9::: %d\n", $ip->isIpInSubnet('fe80:dead:15:9::') ? 1 : 0);
printf("fe80:dead:15:a::: %d\n", $ip->isIpInSubnet('fe80:dead:15:a::') ? 1 : 0);
printf("fe80:dead:15:b::: %d\n", $ip->isIpInSubnet('fe80:dead:15:b::') ? 1 : 0);
?>
--EXPECT--
IPv6: fe80:dead:15:a:bad:1dea::1
CIDR: 64
Subnetmask: ffff:ffff:ffff:ffff::
Netmask: fe80:dead:15:a::
Broadcast: fe80:dead:15:a:ffff:ffff:ffff:ffff
ARPA: 1.0.0.0.0.0.0.0.a.e.d.1.d.a.b.0.a.0.0.0.5.1.0.0.d.a.e.d.0.8.e.f.ip6.arpa
fe80:dead:15:9::: 0
fe80:dead:15:a::: 1
fe80:dead:15:b::: 0
