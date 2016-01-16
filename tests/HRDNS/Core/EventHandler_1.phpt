--TEST--
Testing \HRDNS\Core\EventHandler - multiple events
--FILE--
<?php
$basePath = preg_replace('/\/tests\/.*/','',realpath(__DIR__));
require_once($basePath.'/vendor/autoload.php');

use \HRDNS\Core\EventHandler;

$clicked = 0;
$eventHandler = EventHandler::get();
$eventHandler->addEvent('click',function()use(&$clicked){
    $clicked++;
});
$eventHandler->addEvent('click',function()use(&$clicked){
    $clicked++;
});

var_dump($clicked);
$eventHandler->fireEvent('click');
var_dump($clicked);
?>
--EXPECT--
int(0)
int(2)
