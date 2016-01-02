--TEST--
Testing HRDNS\System\Process\Process - timeout
--FILE--
<?php
$basePath = preg_replace('/\/tests\/.*/','',__DIR__);
require_once($basePath.'/vendor/autoload.php');

use HRDNS\System\Process\Process;

$process = new Process();
$process->setCommand(function(Process $oProcess) {
    sleep(10);
    return Process::EXITCODE_FAILED;
});
$process->start();
sleep(1);
$process->stop();
var_dump($process->getExitCode());
?>
--EXPECT--
int(1026)