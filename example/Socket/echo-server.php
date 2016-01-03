#!/usr/bin/env php
<?php

declare(ticks = 1);

$basePath = preg_replace('/\/example\/.*/', '', __DIR__);
require_once($basePath . '/vendor/autoload.php');

use \HRDNS\Socket\Server\TCPServer;
use \HRDNS\Socket\Server\Client;
use \HRDNS\Core\SignalHandler;

class Server extends TCPServer
{

    /**
     * @param Client $client
     * @return void
     */
    public function onConnect(Client $client)
    {
        printf("Client:%s :: %s\n", $client->getId(), __METHOD__);
    }

    /**
     * @param Client $client
     * @param string $buffer
     * @return void
     */
    public function onIncoming(Client $client, $buffer)
    {
        $buffer = trim($buffer);
        printf("Client:%s :: %s :: %s\n", $client->getId(), __METHOD__, $buffer);
        if (in_array(strtolower($buffer), array ('exit', 'quit', 'bye', 'bye bye', 'byebye'))) {
            $this->disconnect($client, true);
            return;
        }
        $this->send($client, $buffer . "\n");
    }

    /**
     * @param Client $client
     * @param string $buffer
     * @return void
     */
    public function onOutgoing(Client $client, $buffer)
    {
        $buffer = trim($buffer);
        printf("Client:%s :: %s :: %s\n", $client->getId(), __METHOD__, $buffer);
    }

    /**
     * @param Client $client
     * @param bool $closeByPeer
     * @return void
     */
    public function onDisconnect(Client $client, $closeByPeer = false)
    {
        printf(
            "Client:%s :: %s :: connection closed by %s\n",
            $client->getId(),
            __METHOD__,
            ($closeByPeer ? 'peer' : 'server')
        );
    }

    /**
     * @param Client $client
     * @return void
     */
    public function onTick(Client $client)
    {
        static $time;
        $time = isset($time) ? $time : 0;
        if ($time == time()) {
            return;
        }
        $time = time();
        printf("Client:%s :: %s\n", $client->getId(), __METHOD__);
    }

}

try {

    error_reporting(E_ALL | E_STRICT | E_DEPRECATED);
    parse_str(implode('&', array_slice($argv, 1)), $parameter);
    $parameter = is_array($parameter) ? $parameter : array ();
    $parameter['runtime'] = isset($parameter['runtime']) ? (int)$parameter['runtime'] : 5;
    $parameter['listen'] = isset($parameter['listen']) ? $parameter['listen'] : '127.0.0.1';
    $parameter['port'] = isset($parameter['port']) ? $parameter['port'] : mt_rand(50000, 60000);

    $end = time() + $parameter['runtime'] * 60;

    echo "* Create server ... ";
    global $server;
    $server = new \Server();
    echo "DONE\n* Set options ...";
    $server->setListen($parameter['listen']);
    $server->setPort($parameter['port']);
    echo "DONE\n* Bind server on " . $parameter['listen'] . ":" . $parameter['port'] . " ... ";
    $server->bind();
    echo "DONE\n";

    SignalHandler::init();
    SignalHandler::addListner(function ($signal) use ($server) {
        echo "\n\n*** TERMINATED BY SIGNAL " . $signal . " ***\n\n";
        $server->terminated();
        return true;
    });

    while (time() < $end) {
        if ($server->hasTerminated() || SignalHandler::hasTerminated()) {
            break;
        }
        $server->listen(1000);
    }

    exit(0);
} catch (Exception $e) {
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
