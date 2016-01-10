--TEST--
Testing \HRDNS\Socket\Client\TCPClient - check client
--FILE--
<?php
$basePath = preg_replace('/\/tests\/.*/', '', __DIR__);
require_once($basePath . '/vendor/autoload.php');

use \HRDNS\Socket\Client\TCPClient;

try {

    $timeout = 3;

    $result = dns_get_record('gmail.com', DNS_MX);
    $result = isset($result[0]) ? $result[0] : array ();
    $result = isset($result['target']) ? $result['target'] : '';

    if (!$result) {
        echo "FAIL";
        exit(1);
    }

    $client = new TCPClient();
    $client->setHost($result);
    $client->setPort(25);
    $client->setBufferLength(1024);
    $client->setTimeout($timeout, 0);
    $client->connect();

    $end = time() + $timeout;
    $data = false;
    while ($data === false && time() < $end) {
        $data = $client->read();
        if ($data === false) {
            sleep(0.5);
        }
    }
    $client->write("quit\n");
    $client->disconnect();

    echo (preg_match('/^2/', $data) ? "DONE" : "FAIL") . "\n";
} catch (\Exception $e) {
    echo $e->getMessage();
    echo "FAIL\n";
}
?>
--EXPECT--
DONE
