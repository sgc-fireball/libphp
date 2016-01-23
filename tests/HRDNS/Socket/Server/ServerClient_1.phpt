--TEST--
Testing \HRDNS\Socket\Server\ServerClient - set host
--FILE--
<?php
$basePath = preg_replace('/\/tests\/.*/', '', realpath(__DIR__));
require_once($basePath . '/tests/bootstrap.php');

use \HRDNS\Socket\Server\ServerClient;

$host = sprintf('host%d.local', mt_rand(100, 999));

$client = new ServerClient();
$client->setHost($host);

var_dump($client->getHost() === $host);
?>
--EXPECT--
bool(true)