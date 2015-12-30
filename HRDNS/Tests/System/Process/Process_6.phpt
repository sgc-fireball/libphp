--TEST--
Testing HRDNS\System\Process\Process - Exception
--FILE--
<?php
require_once(__DIR__ . '/../../../../vendor/autoload.php');

use HRDNS\System\Process\Process;
$processA = new Process();
$processB = clone($processA);
echo $processA->getId() != $processB->getId() ? 'true' : 'false';
?>
--EXPECT--
true