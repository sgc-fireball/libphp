--TEST--
Testing HRDNS\System\Process\Process - Exception
--FILE--
<?php
$basePath = preg_replace('/\/tests\/.*/','',__DIR__);
require_once($basePath.'/vendor/autoload.php');

use HRDNS\System\Process\Process;

$processA = new Process();
$processB = clone($processA);
echo $processA->getId() != $processB->getId() ? 'true' : 'false';
?>
--EXPECT--
true