--TEST--
Testing \HRDNS\Socket\Server\Client - set port
--FILE--
<?php
$basePath = preg_replace('/\/tests\/.*/','',__DIR__);
require_once($basePath.'/vendor/autoload.php');

use \HRDNS\Socket\Server\Client;

$port = mt_rand(1,65535);

$client = new Client();
$client->setPort($port);

var_dump( $client->getPort() === $port );
?>
--EXPECT--
bool(true)