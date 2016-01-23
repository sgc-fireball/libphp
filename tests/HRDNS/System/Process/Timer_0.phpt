--TEST--
Testing \HRDNS\System\Process\Timer - Timeout
--FILE--
<?php

declare(ticks = 100);

$basePath = preg_replace('/\/tests\/.*/', '', realpath(__DIR__));
require_once($basePath . '/tests/bootstrap.php');

use HRDNS\System\Process\Timer;

$startTime = time();
$timer = Timer::getInstance();
$timer->addTimeout(
    function () use ($startTime) {
        echo (time() - $startTime) . "\n";
    },
    2
);

while (time() - $startTime < 3) {
    usleep(750);
}

?>
--EXPECT--
2
