--TEST--
Testing \HRDNS\Socket\Server\WebSocket\Response - create response
--FILE--
<?php
$basePath = preg_replace('/\/tests\/.*/', '', realpath(__DIR__));
require_once($basePath . '/tests/bootstrap.php');

use \HRDNS\Socket\Server\WebSocket\Response;

$response = new Response('test', 403);
$response->setVersion('1.1');
$response->addHeader('Server', 'PHPUnit-Test');
echo $response;

?>
--EXPECT--
HTTP/1.1 403 Forbidden
Server: PHPUnit-Test
Content-Length: 4

test