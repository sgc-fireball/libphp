# HRDNS libphp [![Build Status](https://travis-ci.org/sgc-fireball/libphp.svg)](https://travis-ci.org/sgc-fireball/libphp)

This branch/lib is only for PHP7.

## Installation

Install the latest version with

```bash
composer require sgc-fireball/libphp
```

## Features
- Event management 
- PCNTL Signal handling
- Color convertions
    - rgb2hsv
    - rgb2hsl
    - rgb2hex
    - rgb2cmyk
    - rgb2xterm
    - hsv2rgb
    - hsv2hex
    - hsv2hsl
    - hsv2cmyk
    - hsl2rgb
    - hsl2hex
    - hsl2cmyk
    - hsl2hsv
    - hex2rgb
    - hex2hsl
    - hex2cmyk
    - hex2hsv
    - cmyk2rgb
    - cmyk2hsl
    - cmyk2hex
    - cmyk2hsv
    - xterm2cmyk
    - xterm2hex
    - xterm2hsl
    - xterm2hsv
    - xterm2rgb
- FTP wrapper
- generic TCP client
- generic UDP client
- SSDP client (Simple Service Discovery Protocol)
- generic TCP server
- generic UDP server
- WebSocket Server
- SSDP server (Simple Service Discovery Protocol)
- SSL server validator (using openssl)
- file wrapper and multiprocess safe file wrapper
- geoip for ipv4 and ipv6 (based on country codes via ripe database)
- interval manager (setinterval in js)
- timer manager (settimeout in js)
- linux daemon component
- multi processing manager with processlist administration
- multiprocessing shared storage via SHM or filesystem.
- fifo buffer
- filo buffer
- rind buffer
- csv wrapper
- ipv4 and ipv6 calculation
    - subnetting and supernetting
- generic struct object
- url decoder and encoder
- xml decoder and encoder

## Examples

### TCP Echo Server
Start the TCP Echo Server in Console 1:
```bash
cd ./libphp
php bin/example.php exmaple:echoserver --help
php bin/example.php exmaple:echoserver --listen 127.0.0.1 --port 12345
```
Start another Console to connect to TCP Echo Server:
```bash
telnet 127.0.0.1 12345
```

### SSL Verify
A single console command to run ssl verification:
```bash
cd ./libphp
php bin/sslverify.php --host=www.google.com --port=443
```

## Tests

### Run all tests

```bash
cd ./libphp
composer test
```

### Test it with PHP Code Sniffer

```bash
cd ./libphp
bin/phpcs
```
   
### Test it with PHPUnit

```bash
cd ./libphp
bin/phpunit
```

### Test it with PHP Copy/Paste Detector

```bash
cd ./libphp
bin/phpcpd src/
```

### Test it with PHP Mess Detector

```bash
cd ./libphp
bin/phpmd src/ text cleancode,codesize,controversial,design,naming,unusedcode --suffixes php
```

## About

### Author

Richard Hülsberg - [rh+github@hrdns.de](mailto:rh+github@hrdns.de) - <https://www.hrdns.de>