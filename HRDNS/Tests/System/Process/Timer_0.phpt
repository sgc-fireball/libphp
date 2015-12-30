--TEST--
Testing \HRDNS\System\Process\Timer - Timeout
--FILE--
<?php

declare(ticks = 10);

require_once(__DIR__ . '/../../../../vendor/autoload.php');

use HRDNS\System\Process\Timer;

$startTime = time();
$timer = Timer::getInstance();
$timer->addTimeout(function()use($startTime){
    echo (time()-$startTime)."\n";
},2);

while (time()-$startTime < 3) {
    usleep(750);
}

?>
--EXPECT--
2