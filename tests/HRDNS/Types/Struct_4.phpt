--TEST--
Testing HRDNS\Types\Struct - load from serialize
--FILE--
<?php
require_once(__DIR__ . '/../../../vendor/autoload.php');
use HRDNS\Types\Struct;

$struct = new Struct(array (
    'test' => 'test'
));
echo $struct->getXML();
?>
--EXPECT--
<?xml version="1.0" encoding="UTF-8" ?>
<struct type="object" class="HRDNS\Types\Struct">
    <test type="string"><![CDATA[test]]></test>
</struct>