--TEST--
Testing \HRDNS\Socket\Server\WebSocket\Request - parse request
--FILE--
<?php declare(strict_types=1);
$basePath = preg_replace('/\/tests\/.*/', '', realpath(__DIR__));
require_once($basePath . '/tests/bootstrap.php');

use \HRDNS\Socket\Server\WebSocket\Request;

$request = Request::parse('GET /ws?test=1 HTTP/1.1
Host: 192.168.2.175:8080
Connection: Upgrade
Pragma: no-cache
Cache-Control: no-cache
Upgrade: websocket
Origin: http://www.heise.de
Sec-WebSocket-Version: 13
DNT: 1
User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.29 Safari/537.36
Accept-Encoding: gzip, deflate, sdch
Accept-Language: de-DE,de;q=0.8,en;q=0.6
Sec-WebSocket-Key: C5PqKGvj5zxmmmBbmI3ASw==
Sec-WebSocket-Extensions: permessage-deflate; client_max_window_bits');

print_r ( $request );

?>
--EXPECT--
HRDNS\Socket\Server\WebSocket\Request Object
(
    [method:HRDNS\Socket\Server\WebSocket\Request:private] => GET
    [path:HRDNS\Socket\Server\WebSocket\Request:private] => /ws
    [query:HRDNS\Socket\Server\WebSocket\Request:private] => test=1
    [version:HRDNS\Socket\Server\WebSocket\Request:private] => 1.1
    [header:HRDNS\Socket\Server\WebSocket\Request:private] => Array
        (
            [host] => 192.168.2.175:8080
            [connection] => Upgrade
            [pragma] => no-cache
            [cache-control] => no-cache
            [upgrade] => websocket
            [origin] => http://www.heise.de
            [sec-websocket-version] => 13
            [dnt] => 1
            [user-agent] => Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.29 Safari/537.36
            [accept-encoding] => gzip, deflate, sdch
            [accept-language] => de-DE,de;q=0.8,en;q=0.6
            [sec-websocket-key] => C5PqKGvj5zxmmmBbmI3ASw==
            [sec-websocket-extensions] => permessage-deflate; client_max_window_bits
        )

)