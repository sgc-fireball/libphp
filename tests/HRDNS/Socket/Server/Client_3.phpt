--TEST--
Testing \HRDNS\Socket\Server\Client - set attributes
--FILE--
<?php
$basePath = preg_replace('/\/tests\/.*/','',__DIR__);
require_once($basePath.'/vendor/autoload.php');

use \HRDNS\Socket\Server\Client;

$key = 'key'.mt_rand(1,65535);
$value = mt_rand(1,65535);

$client = new Client();
$client->setAttribute($key,$value);

var_dump( $client->getAttribute($key) === $value );
?>
--EXPECT--
bool(true)