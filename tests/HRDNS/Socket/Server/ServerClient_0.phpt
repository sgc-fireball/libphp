--TEST--
Testing \HRDNS\Socket\Server\ServerClient - check client id
--FILE--
<?php
$basePath = preg_replace('/\/tests\/.*/','',__DIR__);
require_once($basePath.'/vendor/autoload.php');

use \HRDNS\Socket\Server\ServerClient;

$client1 = new ServerClient();
$client2 = new ServerClient();

var_dump( $client1->getId() !== $client2->getId() );
?>
--EXPECT--
bool(true)