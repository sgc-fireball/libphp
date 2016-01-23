--TEST--
Testing \HRDNS\System\Process\Daemon - Interval
--FILE--
<?php

$basePath = preg_replace('/\/tests\/.*/', '', realpath(__DIR__));
require_once($basePath . '/tests/bootstrap.php');

use HRDNS\System\Process\Daemon;

try {
    $daemon = Daemon::getInstance();
    $daemon->setPidPath('.');
    if ($daemon->getProcessName() == 'Daemon') {
        $daemon->setProcessName('Daemon_0');
    }
    printf("ProcessName: %s\n", $daemon->getProcessName());
    printf("PidDirPath: %s\n", $daemon->getPidPath());
    printf("PidFilePath: %s\n", $daemon->getPidPathname());

    printf("IsDaemonRunning: %s\n", $daemon->isDaemonAlreadyRunning() ? 'true' : 'false');

    $daemon->start();

    printf("IsDaemonRunning: %s\n", $daemon->isDaemonAlreadyRunning() ? 'true' : 'false');

    $daemon->stop();

    printf("IsDaemonRunning: %s\n", $daemon->isDaemonAlreadyRunning() ? 'true' : 'false');
} catch (Exception $e) {
    printf("ERROR[%d] %s\n%s\n", $e->getCode(), $e->getMessage(), $e->getTraceAsString());
}

?>
--EXPECT--
ProcessName: Daemon_0
PidDirPath: .
PidFilePath: ./daemon.pid
IsDaemonRunning: false
IsDaemonRunning: true
IsDaemonRunning: false
