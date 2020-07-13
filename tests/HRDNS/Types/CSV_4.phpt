--TEST--
Testing HRDNS\Types\CSV - ArrayAccess
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
file_put_contents($tmp, implode('|', array_keys($test)) . "\n");
file_put_contents($tmp, implode('|', $test), FILE_APPEND);

$csv = new CSV($tmp, "|");
$csv->open();

echo implode('-',$csv[1]);

unlink($tmp);
?>
--EXPECT--
Value1-Value2-Value3-Value4