--TEST--
Testing HRDNS\System\Network\RIPE\Database - Test download/decompress/convert
--FILE--
<?php
$basePath = preg_replace('/\/tests\/.*/', '', realpath(__DIR__));
require_once($basePath . '/tests/bootstrap.php');

exit(0);
// @TODO

use HRDNS\System\Network\RIPE\Database;

$file = tempnam('/tmp/', 'phpunit');
$db = new Database();
$db->download($file)->decompress($file)->convert($file);
unlink($file);

?>
--EXPECT--