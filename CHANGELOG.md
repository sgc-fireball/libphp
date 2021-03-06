# CHANGELOG

## 2020-07-12
- reimplement IMAP

## 2017-05-22
- implement testing color functions: rgb2xyb, xyb2rgb for philips hue lamps
- skip homematic negative float and integer tests

## 2017-03-12
- fix binrpc protocol

## 2017-01-22
- improve tests

## 2017-01-08 *v1.1.1*
- downgrade phpdox to 0.8.x (@stable is currently broken)

## 2017-01-08 *v1.1.0*
- fixed homematic binrpc float and integer negative numbers
- added redis based message queue
- fixed test namespaces

## 2017-01-07
- merge jenkins-ci branch into master (run ```composer build```)

## 2016-12-30
- implement HomeMatic binrpc decoder/encoder

## 2016-12-29
- implement incoming network sniffer (icmp, tcp, udp)

## 2016-11-04
- update readme.md
- refactor some tests

## *v1.0.0* - php7 to master
- php7 is now the minimum version level!

## 2016-10-29
- adding simple service discover protocol (ssdp) to find universal plug and play (upnp) services
- adding ssdp client example
- fixing code styles
- refactor php-test to phpunit tests

## 2016-09-25
- adding xterm color conversion

## 2016-05-01
- adding WebSocketServer (unstable/testing)

## 2016-04-27
- adding URL support
- adding RIPE Database converter

## 2016-04-26
- adding CSV support

## 2016-04-23
- adding IPv6 supports (HRDNS\Types\IPv6)

## 2016-04-22
- adding FTP supports (HRDNS\Protocol\FTP)
- adding IPv4 supports (HRDNS\Types\IPv4)
- change composer test definition

## 2016-04-17
- fix some phpmd issues (incompatible to old libphp version!)

## 2016-04-16
- update php7 return values
- reduce duplicate code in src/HRDNS/General/Color.php

## 2016-04-14
- remove test/HRDNS/SSL/Validator_0.phpt 

## 2016-04-12
- update to PHP7

## 2016-01-23
- remove old example
- implement symfony/console
- implement symfony command example
- update code style in tests, examples, bin
- implement single symfony command sslverify
- fixing doc types

## 2016-01-21
- fixing method names cymk2* and *2cymk
- implement phpmd
- update phpcs and phpunit configurations
- update phpdoc "@return self" and "@return $this"

## 2016-01-16 
- fixing travis.ci checks
- implement Daemon test
- fix require autoload.php

## 2016-01-15
- require phpcpd
- optimize code
- rename Client to ServerClient

## 2016-01-10
- implement travis-ci.org support
- implement TCPClient with tests
- implement UDPClient & Server with tests

## 2016-01-03 _v0.0.1_
- adding TCP Server with tests
- adding example echo-server.php

## 2016-01-02
- add CHANGELOG.md
- add EventHandler
- update tree structur 
- update ```phpunit``` und ```phpcs``` configs

## 2015-12-30
- initial check-in
