--TEST--
Testing \HRDNS\Socket\Server\Client - check client id
--FILE--
<?php
$basePath = preg_replace('/\/tests\/.*/','',__DIR__);
require_once($basePath.'/vendor/autoload.php');

use \HRDNS\Socket\Server\Client;

$client1 = new Client();
$client2 = new Client();

var_dump( $client1->getId() !== $client2->getId() );
?>
--EXPECT--
bool(true)