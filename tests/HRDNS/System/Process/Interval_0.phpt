--TEST--
Testing \HRDNS\System\Process\Timer - Interval
--FILE--
<?php

declare(ticks = 100);

$basePath = preg_replace('/\/tests\/.*/', '', realpath(__DIR__));
require_once($basePath . '/tests/bootstrap.php');

use HRDNS\System\Process\Timer;

$count = 0;
$startTime = time();
$timer = Timer::getInstance();
$timer->addInterval(
    function () {
        global $count;
        if ($count >= 3) {
            return;
        }
        $count++;
        echo $count."\n";
    },
    1
);

while (time() - $startTime < 4) {
    usleep(50);
}
?>
--EXPECT--
1
2
3
