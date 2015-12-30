--TEST--
Testing HRDNS\Types\Struct - load from serialize
--FILE--
<?php
require_once(__DIR__ . '/../../../vendor/autoload.php');
use HRDNS\Types\Struct;
$struct = new Struct();
$struct->loadFromSerialize('a:1:{s:4:"test";s:4:"test";}');
echo $struct->getSerialize();
?>
--EXPECT--
a:1:{s:4:"test";s:4:"test";}