--TEST--
Testing HRDNS\System\Process\Process - Exception
--FILE--
<?php declare(strict_types=1);
$basePath = preg_replace('/\/tests\/.*/', '', realpath(__DIR__));
require_once($basePath . '/tests/bootstrap.php');

use HRDNS\System\Process\Process;

$processA = new Process();
$processB = clone($processA);
echo $processA->getId() != $processB->getId() ? 'true' : 'false';
?>
--EXPECT--
true
