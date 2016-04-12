--TEST--
Testing \HRDNS\Socket\Server\UDPServer - client connect
--FILE--
<?php
$basePath = preg_replace('/\/tests\/.*/', '', realpath(__DIR__));
require_once($basePath . '/tests/bootstrap.php');

use \HRDNS\Socket\Server\UDPServer;
use \HRDNS\Socket\Server\ServerClient;

class Server extends UDPServer
{

    /**
     * @param ServerClient $client
     * @return void
     */
    public function onConnect(ServerClient $client)
    {
        echo __METHOD__ . "\n";
    }

    /**
     * @param ServerClient $client
     * @param string $buffer
     * @return void
     */
    public function onIncoming(ServerClient $client, string $buffer)
    {
        printf("%s - %s\n", __METHOD__, trim($buffer));
        $msg = (trim($buffer) == 'foo' ? 'bar' : '???') . "\n";
        $this->send($client, $msg);
    }

    /**
     * @param ServerClient $client
     * @param string $buffer
     * @return void
     */
    public function onOutgoing(ServerClient $client, string $buffer)
    {
        printf("%s - %s\n", __METHOD__, trim($buffer));
    }

    /**
     * @param ServerClient $client
     * @param bool $closeByPeer
     * @return void
     */
    public function onDisconnect(ServerClient $client, bool $closeByPeer = false)
    {
        echo __METHOD__ . "\n";
    }

    /**
     * @param ServerClient $client
     * @return void
     */
    public function onTick(ServerClient $client)
    {
        static $time;
        $time = isset($time) ? $time : 0;
        if ($time == time()) {
            return;
        }
        $time = time();
        echo __METHOD__ . "\n";
    }

}

try {
    $port = mt_rand(40000, 50000);
    $server = new \Server();
    $server->setListen('127.0.0.1');
    $server->setPort($port);
    $server->bind();
    $server->listen(1);

    $client = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    socket_connect($client, '127.0.0.1', $port);

    $server->listen(1);
    socket_write($client, "foo\n");

    $server->listen(1);
    $data = trim(socket_read($client, 8192));
    var_dump($data);

    socket_close($client);

} catch (\Exception $e) {
    printf("ERROR[%d] %s\n%s\n", $e->getCode(), $e->getMessage(), $e->getTraceAsString());
}
?>
--EXPECT--
Server::onConnect
Server::onIncoming - foo
Server::onOutgoing - bar
Server::onTick
string(3) "bar"
Server::onDisconnect
