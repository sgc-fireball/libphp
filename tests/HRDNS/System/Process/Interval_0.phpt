--TEST--
Testing \HRDNS\System\Process\Timer - Interval
--FILE--
<?php declare(strict_types=1);

$basePath = preg_replace('/\/tests\/.*/', '', realpath(__DIR__));
require_once($basePath . '/tests/bootstrap.php');

use HRDNS\System\Process\Timer;

$count = 0;
$startTime = time();
$timer = Timer::getInstance();
$timer->addInterval(
    function () {
        global $count;
        $count++;
        echo $count."\n";
    },
    1
);

sleep(1);
$timer->tick();
sleep(1);
$timer->tick();
sleep(1);
$timer->tick();

?>
--EXPECT--
1
2
3
