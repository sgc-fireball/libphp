--TEST--
Testing \HRDNS\Socket\Server\ServerClient - set port
--FILE--
<?php declare(strict_types=1);
$basePath = preg_replace('/\/tests\/.*/', '', realpath(__DIR__));
require_once($basePath . '/tests/bootstrap.php');

use \HRDNS\Socket\Server\ServerClient;

$port = mt_rand(1, 65535);

$client = new ServerClient();
$client->setPort($port);

var_dump($client->getPort() === $port);
?>
--EXPECT--
bool(true)