--TEST--
Testing HRDNS\Types\CSV - Iterator write
--FILE--
<?php declare(strict_types=1);
$basePath = preg_replace('/\/tests\/.*/', '', realpath(__DIR__));
require_once($basePath . '/tests/bootstrap.php');

use \HRDNS\Types\CSV;

$test = [
    'Header1' => 'Value1',
    'Header2' => 'Value2',
    'Header3' => 'Value3',
    'Header4' => 'Value4',
];

$tmp = tempnam('/tmp/', 'phpunit');
$csv = new CSV($tmp);
$csv->open();
$csv->write(array_keys($test));
$csv->write($test);
$csv->close();

echo file_get_contents($tmp);
unlink($tmp);
?>
--EXPECT--
Header1,Header2,Header3,Header4
Value1,Value2,Value3,Value4