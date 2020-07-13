--TEST--
Testing \HRDNS\Socket\Client\UDPClient - check client
--FILE--
<?php declare(strict_types=1);
$basePath = preg_replace('/\/tests\/.*/', '', realpath(__DIR__));
require_once($basePath . '/tests/bootstrap.php');

use \HRDNS\Socket\Client\UDPClient;

try {

    $timeout = 3;

    $host = array ('google', 'de');

    $data = '';
    $data .= pack('n', rand(1, 16000));
    $data .= pack('n', 0x0000 | (0x0100 & 0x0300));
    $data .= pack('nnnn', 1, 0, 0, 0);
    foreach ($host as $part) {
        $data .= pack('C', mb_strlen($part));
        $data .= $part;
    }
    $data .= pack('C', 0);
    $data .= pack('n', 255);
    $data .= pack('n', 1);

    $client = new UDPClient();
    $client->setHost('8.8.8.8');
    $client->setPort(53);
    $client->setBufferLength(1024);
    $client->setTimeout($timeout, 0);
    $client->connect();
    $client->write($data);

    $end = time() + $timeout;
    $data = false;
    while ($data === false && time() < $end) {
        $data = $client->read();
        if ($data === false) {
            usleep(500000);
        }
    }
    $client->disconnect();

    echo (preg_match('/google/', $data) ? 'DONE' : 'FAIL') . "\n";

} catch (\Exception $e) {
    printf("ERROR[%d] %s\n%s\n", $e->getCode(), $e->getMessage(), $e->getTraceAsString());
}
?>
--EXPECT--
DONE
