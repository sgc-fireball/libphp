--TEST--
Testing \HRDNS\System\Process\Timer - Timeout
--FILE--
<?php

$basePath = preg_replace('/\/tests\/.*/', '', realpath(__DIR__));
require_once($basePath . '/tests/bootstrap.php');

use HRDNS\System\Process\Timer;

$startTime = time();
$timer = Timer::getInstance();
$timer->addTimeout(
    function () use ($startTime) {
        echo (time() - $startTime) . "\n";
    },
    1
);

sleep(1);
$timer->tick();

?>
--EXPECT--
1