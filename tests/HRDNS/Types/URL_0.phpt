--TEST--
Testing HRDNS\Types\URL - test1
--FILE--
<?php
$basePath = preg_replace('/\/tests\/.*/', '', realpath(__DIR__));
require_once($basePath . '/tests/bootstrap.php');

use \HRDNS\Types\URL;

$url = new URL();
$url->setURL('http://www.google.de');

echo 'Scheme:' . $url->getScheme() . "\n";
echo 'User:' . $url->getUser() . "\n";
echo 'Pass:' . $url->getPassword() . "\n";
echo 'Host:' . $url->getHost() . "\n";
echo 'Port:' . $url->getPort() . "\n";
echo 'Path:' . $url->getPath() . "\n";
echo 'Query:' . $url->getQuery() . "\n";
echo 'Fragment:' . $url->getFragment() . "\n";
echo 'URL:' . $url->getURL() . "\n";
?>
--EXPECT--
Scheme:http
User:
Pass:
Host:www.google.de
Port:80
Path:/
Query:
Fragment:
URL:http://www.google.de/