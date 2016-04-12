--TEST--
Testing \HRDNS\Socket\Server\TCPServer - check bind
--FILE--
<?php
$basePath = preg_replace('/\/tests\/.*/', '', realpath(__DIR__));
require_once($basePath . '/tests/bootstrap.php');

use \HRDNS\Socket\Server\TCPServer;
use \HRDNS\Socket\Server\ServerClient;

class Server extends TCPServer
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
        echo __METHOD__ . "\n";
    }

    /**
     * @param ServerClient $client
     * @param string $buffer
     * @return void
     */
    public function onOutgoing(ServerClient $client, string $buffer)
    {
        echo __METHOD__ . "\n";
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
        echo __METHOD__ . "\n";
    }

}

try {
    $server = new \Server();
    $server->setListen('127.0.0.1');
    $server->setPort(mt_rand(40000, 50000));
    $server->bind();
    $server->listen(10);
    echo "DONE\n";
} catch (\Exception $e) {
    printf("ERROR[%d] %s\n%s\n", $e->getCode(), $e->getMessage(), $e->getTraceAsString());
}
?>
--EXPECT--
DONE
