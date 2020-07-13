--TEST--
Testing \HRDNS\Socket\Server\ServerClient - set attributes
--FILE--
<?php declare(strict_types=1);
$basePath = preg_replace('/\/tests\/.*/', '', realpath(__DIR__));
require_once($basePath . '/tests/bootstrap.php');

use \HRDNS\Socket\Server\ServerClient;

$key = 'key' . mt_rand(1, 65535);
$value = mt_rand(1, 65535);

$client = new ServerClient();
$client->setAttribute($key, $value);

var_dump($client->getAttribute($key) === $value);
?>
--EXPECT--
bool(true)