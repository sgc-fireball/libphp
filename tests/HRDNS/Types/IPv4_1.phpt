--TEST--
Testing HRDNS\Types\IPv4 - ipv4 check setSubnetmask
--FILE--
<?php
$basePath = preg_replace('/\/tests\/.*/', '', realpath(__DIR__));
require_once($basePath . '/tests/bootstrap.php');

use \HRDNS\Types\IPv4;

$ip = new IPv4('192.168.2.1', 24);
$ip->setSubnetmask('255.255.0.0');

printf("IPv4: %s\n", $ip->getIp());
printf("IPv6: %s\n", $ip->getIpv6());
printf("CIDR: %s\n", $ip->getCIDR());
printf("Subnetmask: %s\n", $ip->getSubnetmask());
printf("Netmask: %s\n", $ip->getNetmask());
printf("Broadcast: %s\n", $ip->getBroadcast());
printf("ARPA: %s\n", $ip->getInArpa());
printf("192.167.1.128: %d\n", $ip->isIpInSubnet('192.167.1.128') ? 1 : 0);
printf("192.168.2.128: %d\n", $ip->isIpInSubnet('192.168.2.128') ? 1 : 0);
printf("192.169.3.128: %d\n", $ip->isIpInSubnet('192.169.3.128') ? 1 : 0);
?>
--EXPECT--
IPv4: 192.168.2.1
IPv6: 2002:00C0:00A8:0002:0001::
CIDR: 16
Subnetmask: 255.255.0.0
Netmask: 192.168.0.0
Broadcast: 192.168.255.255
ARPA: 1.2.168.192.in-addr.arpa
192.167.1.128: 0
192.168.2.128: 1
192.169.3.128: 0
