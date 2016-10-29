--TEST--
Testing HRDNS\Types\CSV - Iterator quote
--FILE--
<?php
$basePath = preg_replace('/\/tests\/.*/', '', realpath(__DIR__));
require_once($basePath . '/tests/bootstrap.php');

use \HRDNS\Types\CSV;

$test = [
    'Header1' => "Val\"ue1",
    'Header2' => "Val\"\nue2",
    'Header3' => "Val\"ue3",
    'Header4' => "Val\"\nue4",
];

$tmp = tempnam('/tmp/', 'phpunit');
$csv = new CSV($tmp, ",");
$csv->open();
$csv->write(array_keys($test));
$csv->write($test);
$csv->close();

echo file_get_contents($tmp);
unlink($tmp);
?>
--EXPECT--
Header1,Header2,Header3,Header4
"Val""ue1","Val""
ue2","Val""ue3","Val""
ue4"