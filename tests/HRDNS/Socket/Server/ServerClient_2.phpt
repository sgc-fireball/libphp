--TEST--
Testing \HRDNS\Socket\Server\ServerClient - set port
--FILE--
<?php
$basePath = preg_replace('/\/tests\/.*/','',realpath(__DIR__));
require_once($basePath.'/vendor/autoload.php');

use \HRDNS\Socket\Server\ServerClient;

$port = mt_rand(1,65535);

$client = new ServerClient();
$client->setPort($port);

var_dump( $client->getPort() === $port );
?>
--EXPECT--
bool(true)