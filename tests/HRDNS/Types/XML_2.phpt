--TEST--
Testing \HRDNS\Types\XML - create xml
--FILE--
<?php declare(strict_types=1);
$basePath = preg_replace('/\/tests\/.*/', '', realpath(__DIR__));
require_once($basePath . '/tests/bootstrap.php');

use \HRDNS\Types\XML;

$child = new XML();
$child->setName('child');
$child->setAttribute('attr', '1');
$child->setCData(true);
$child->setValue('2');

$xml = new XML();
$xml->setRoot();
$xml->setCharset('UTF-8');
$xml->setName('data');
$xml->appendChild($child);

echo $xml->getXML();
?>
--EXPECT--
<?xml version="1.0" encoding="UTF-8" ?>
<data>
    <child attr="1"><![CDATA[2]]></child>
</data>
