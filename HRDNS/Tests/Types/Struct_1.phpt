--TEST--
Testing HRDNS\Types\Struct - load from json
--FILE--
<?php
require_once(__DIR__ . '/../../../vendor/autoload.php');
use HRDNS\Types\Struct;
$struct = new Struct();
$struct->loadFromJSON('{"test":"test"}');
echo $struct->getJSON();
?>
--EXPECT--
{"test":"test"}