# HRDNS libphp [![Build Status](https://travis-ci.org/sgc-fireball/libphp.svg)](https://travis-ci.org/sgc-fireball/libphp)

## Installation

Install the latest version with

```bash
composer require sgc-fireball/libphp
```

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
phpcs
```
   
### Test it with PHPUnit

```bash
cd ./libphp
phpunit
```

## About

### Author

Richard Hülsberg - [rh+github@hrdns.de](mailto:rh+github@hrdns.de) - <https://www.hrdns.de>