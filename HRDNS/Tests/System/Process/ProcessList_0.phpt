--TEST--
Testing HRDNS\System\Process\ProcessList - SIGKILL
--FILE--
<?php

require_once(__DIR__ . '/../../../../vendor/autoload.php');

use HRDNS\System\Process\ProcessList;
use HRDNS\System\Process\Process;

$processA = new Process();
$processA->setCommand(function(Process $oProcess) {
    sleep(10);
    return Process::EXITCODE_SUCCESSFUL;
});
$processB = clone($processA);

$oProcessList = new ProcessList();
$oProcessList->setWorker(2);
$oProcessList->addProcess($processA);
$oProcessList->addProcess($processB);
$oProcessList->start();
sleep(1);
$oProcessList->stop(SIGTERM,1);

printf("ProcessA: %d\nProcessB: %d",
    $processA->getExitCode(),
    $processB->getExitCode()
);

?>
--EXPECT--
ProcessA: 1026
ProcessB: 1026