--TEST--
Testing HRDNS\System\Process\Process - Exception
--FILE--
<?php
$basePath = preg_replace('/\/tests\/.*/','',realpath(__DIR__));
require_once($basePath.'/vendor/autoload.php');

use HRDNS\System\Process\Process;

$process = new Process();
$process->setCommand(function(Process $process) { throw new Exception('Exit',10); });
$process->start();

do { usleep(500); } while ($process->isRunning());

var_dump($process->getExitCode());
?>
--EXPECT--
int(10)