--TEST--
Testing \HRDNS\System\Process\Timer - Interval
--FILE--
<?php

declare(ticks = 10);

$basePath = preg_replace('/\/tests\/.*/','',realpath(__DIR__));
require_once($basePath.'/vendor/autoload.php');

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