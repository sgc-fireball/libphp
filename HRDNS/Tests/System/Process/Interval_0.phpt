--TEST--
Testing \HRDNS\System\Process\Timer - Interval
--FILE--
<?php

declare(ticks = 10);

require_once(__DIR__ . '/../../../../vendor/autoload.php');

use HRDNS\System\Process\Timer;

$startTime = time();
$timer = Timer::getInstance();
$timer->addInterval(function()use($startTime){
    echo (time()-$startTime)."\n";
},1);

while (time()-$startTime < 4) {
    usleep(250);
}

?>
--EXPECT--
1
2
3