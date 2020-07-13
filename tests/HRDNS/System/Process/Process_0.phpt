--TEST--
Testing HRDNS\System\Process\Process - timeout
--FILE--
<?php declare(strict_types=1);
$basePath = preg_replace('/\/tests\/.*/', '', realpath(__DIR__));
require_once($basePath . '/tests/bootstrap.php');

use HRDNS\System\Process\Process;

$process = new Process();
$process->setCommand(
    function (Process $oProcess) {
        sleep(10);
        return Process::EXITCODE_FAILED;
    }
);
$process->start();
sleep(1);
$process->stop();

var_dump($process->getExitCode());
?>
--EXPECT--
int(1026)
