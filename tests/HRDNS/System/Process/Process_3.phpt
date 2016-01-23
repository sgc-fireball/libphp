--TEST--
Testing HRDNS\System\Process\Process - include return
--FILE--
<?php
$basePath = preg_replace('/\/tests\/.*/', '', realpath(__DIR__));
require_once($basePath . '/tests/bootstrap.php');

use HRDNS\System\Process\Process;

$tmpFile = tempnam('/tmp', 'phpunit');
file_put_contents($tmpFile, '<?php return 10; ?>');

$process = new Process();
$process->setCommand($tmpFile);
$process->start();

do {
    usleep(500);
} while ($process->isRunning());

var_dump($process->getExitCode());
unlink($tmpFile);
?>
--EXPECT--
int(10)
