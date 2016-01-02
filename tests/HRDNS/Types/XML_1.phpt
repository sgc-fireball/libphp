--TEST--
Testing \HRDNS\Types\XML - get node
--FILE--
<?php
require_once(__DIR__ . '/../../../vendor/autoload.php');
use \HRDNS\Types\XML;
$xml = new XML('<root><child><subchild>1</subchild><subchild>2</subchild></child></root>');
echo $xml->getNode('root..child..subchild;1')->getValue()."\n";
echo $xml->getNode('child..subchild;1')->getValue()."\n";
?>
--EXPECT--
2
2
