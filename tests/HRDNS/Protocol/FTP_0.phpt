--TEST--
Testing \HRDNS\Protocol\FTP - client connect
--FILE--
<?php
$basePath = preg_replace('/\/tests\/.*/', '', realpath(__DIR__));
require_once($basePath . '/tests/bootstrap.php');

use \HRDNS\Protocol\FTP;

try {

    $oDateTime = new \DateTime('yesterday');

    $ftp = (new FTP())
        ->setHost('ftp.ripe.net')
        ->setPort(21)
        ->setUser('anonymous')
        ->setPassword('anonymous@anonymous')
        ->connect()
        ->login()
        ->passiv()
        ->cd('/ripe/stats/' . $oDateTime->format('Y') . '/');
    $list = $ftp->dir();
    $ftp->disconnect();

    var_dump((bool)count($list));

} catch (\Exception $e) {
    printf(
        "%s[%d] %s\n%s\n",
        get_class($e),
        $e->getCode(),
        $e->getMessage(),
        $e->getTraceAsString()
    );
}
?>
--EXPECT--
bool(true)
