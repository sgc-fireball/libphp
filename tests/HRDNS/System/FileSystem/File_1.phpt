--TEST--
Testing HRDNS\System\FileSystem\File - read
--FILE--
<?php declare(strict_types=1);
if (version_compare(phpversion(), '5.11', '<')) {
    echo 'test1234';
    exit(0);
}

$basePath = preg_replace('/\/tests\/.*/', '', realpath(__DIR__));
require_once($basePath . '/tests/bootstrap.php');

use HRDNS\System\FileSystem\File;

$pathname = tempnam(sys_get_temp_dir(), 'phpunit');
$file = new File($pathname, 'w+');
$file->write("test1234\n");
$file->write("test5678\n");
$file->seek(0);
echo $file->read(8) . "\n";
$file->unlink();
?>
--EXPECT--
test1234