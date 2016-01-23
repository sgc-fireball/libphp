--TEST--
Testing \HRDNS\Socket\Server\ServerClient - set socket
--FILE--
<?php
$basePath = preg_replace('/\/tests\/.*/', '', realpath(__DIR__));
require_once($basePath . '/tests/bootstrap.php');

use \HRDNS\Socket\Server\ServerClient;

$client = new ServerClient();
$client->setSocket('Asdasd');

var_dump($client->getSocket() === null);
?>
--EXPECT--
bool(true)