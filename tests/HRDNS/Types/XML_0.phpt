--TEST--
Testing \HRDNS\Types\XML - parse xmll
--FILE--
<?php
require_once(__DIR__ . '/../../../vendor/autoload.php');
use \HRDNS\Types\XML;

$xml = new XML('<root><child attr="2">1</child></root>');
printf("Root: %s\n", $xml->getName());
printf("Children: %s\n", count($xml->getChildren()));
$child = $xml->getNode('child');
printf("Child: %s\n", $child->getName());
printf("Child-Attr: %s\n", $child->getAttribute('attr'));
printf("Child-Content: %s\n", $child->getValue());
?>
--EXPECT--
Root: root
Children: 1
Child: child
Child-Attr: 2
Child-Content: 1
