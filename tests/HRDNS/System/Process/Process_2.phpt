--TEST--
Testing HRDNS\System\Process\Process - exit
--FILE--
<?php
$basePath = preg_replace('/\/tests\/.*/', '', realpath(__DIR__));
require_once($basePath . '/tests/bootstrap.php');

use HRDNS\System\Process\Process;

$process = new Process();
$process->setCommand(
    function (Process $oProcess) {
        exit(10);
    }
);
$process->start();
do {
    usleep(500);
} while ($process->isRunning());

var_dump($process->getExitCode());
?>
--EXPECT--
int(10)
