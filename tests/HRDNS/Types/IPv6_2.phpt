--TEST--
Testing HRDNS\Types\IPv6 - ipv6 long check
--FILE--
<?php
$basePath = preg_replace('/\/tests\/.*/', '', realpath(__DIR__));
require_once($basePath . '/tests/bootstrap.php');

use \HRDNS\Types\IPv6;

$ip = new IPv6('fe80:dead:0015:000a:0bad:1dea:0000:0001');

printf("IPv6: %s\n", $ip->getIp());

$long = $ip->getLong();
$ip->setLong($long);

printf("Long: %s\n", $long);
printf("IPv6: %s\n", $ip->getIp());
?>
--EXPECT--
IPv6: fe80:dead:15:a:bad:1dea::1
Long: 338293041328690602920104126360248647681
IPv6: fe80:dead:15:a:bad:1dea::1
