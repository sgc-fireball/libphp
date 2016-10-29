--TEST--
Testing HRDNS\Types\Struct - load from serialize
--FILE--
<?php
$basePath = preg_replace('/\/tests\/.*/', '', realpath(__DIR__));
require_once($basePath . '/tests/bootstrap.php');

use HRDNS\Types\Struct;

$structA = new Struct(
    array (
        'testa' => new Struct(
            array (
                'testb' => 'testB'
            )
        )
    )
);
echo $structA->getXML();
?>
--EXPECT--
<?xml version="1.0" encoding="UTF-8" ?>
<struct type="object" class="HRDNS\Types\Struct">
    <testa type="object" class="HRDNS\Types\Struct">
        <testb type="string"><![CDATA[testB]]></testb>
    </testa>
</struct>