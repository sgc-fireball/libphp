--TEST--
Testing \HRDNS\Types\XML - parse xmll
--FILE--
<?php declare(strict_types=1);
$basePath = preg_replace('/\/tests\/.*/', '', realpath(__DIR__));
require_once($basePath . '/tests/bootstrap.php');

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
