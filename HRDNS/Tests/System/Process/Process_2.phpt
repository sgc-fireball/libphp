--TEST--
Testing HRDNS\System\Process\Process - exit
--FILE--
<?php
require_once(__DIR__ . '/../../../../vendor/autoload.php');
use HRDNS\System\Process\Process;
$process = new Process();
$process->setCommand(function(Process $oProcess) {
    exit(10);
});
$process->start();
do {
    usleep(500);
} while ($process->isRunning());
var_dump($process->getExitCode());
?>
--EXPECT--
int(10)