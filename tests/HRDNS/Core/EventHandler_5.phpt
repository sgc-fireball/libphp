--TEST--
Testing \HRDNS\Core\EventHandler - shutdown event
--FILE--
<?php
$basePath = preg_replace('/\/tests\/.*/','',realpath(__DIR__));
require_once($basePath.'/vendor/autoload.php');

use \HRDNS\Core\EventHandler;

$eventHandler = EventHandler::get();
$eventHandler->addEvent('shutdown',function()use(&$ticks){
    var_dump(true);
});

?>
--EXPECT--
bool(true)
