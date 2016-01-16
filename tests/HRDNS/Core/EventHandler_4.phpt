--TEST--
Testing \HRDNS\Core\EventHandler - tick event
--FILE--
<?php
declare(ticks=100);
$basePath = preg_replace('/\/tests\/.*/','',realpath(__DIR__));
require_once($basePath.'/vendor/autoload.php');

use \HRDNS\Core\EventHandler;

$ticks = 0;
$eventHandler = EventHandler::get();
$eventHandler->addEvent('tick',function()use(&$ticks){
    $ticks++;
});

for ($i=0;$i<100;$i++) {
    usleep(10);
}

var_dump( $ticks > 0 );
?>
--EXPECT--
bool(true)
