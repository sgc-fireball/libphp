--TEST--
Testing \HRDNS\Socket\Server\TCPServer - check bind
--FILE--
<?php
$basePath = preg_replace('/\/tests\/.*/','',__DIR__);
require_once($basePath.'/vendor/autoload.php');

use \HRDNS\Socket\Server\UDPServer;
use \HRDNS\Socket\Server\Client;

class Server extends UDPServer
{

    /**
     * @param Client $client
     * @return void
     */
    public function onConnect(Client $client)
    {
        echo __METHOD__."\n";
    }

    /**
     * @param Client $client
     * @param string $buffer
     * @return void
     */
    public function onIncoming(Client $client, $buffer)
    {
        echo __METHOD__."\n";
    }

    /**
     * @param Client $client
     * @param string $buffer
     * @return void
     */
    public function onOutgoing(Client $client, $buffer)
    {
        echo __METHOD__."\n";
    }

    /**
     * @param Client $client
     * @param bool $closeByPeer
     * @return void
     */
    public function onDisconnect(Client $client, $closeByPeer = false)
    {
        echo __METHOD__."\n";
    }

    /**
     * @param Client $client
     * @return void
     */
    public function onTick(Client $client)
    {
        echo __METHOD__."\n";
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
    echo "FAIL\n";
}
?>
--EXPECT--
DONE
