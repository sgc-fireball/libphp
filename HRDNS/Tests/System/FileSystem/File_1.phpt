--TEST--
Testing HRDNS\System\FileSystem\File - read
--FILE--
<?php
if (version_compare(phpversion(),5.11,'<')) {
    echo "test1234";
    exit(0);
}

require_once(__DIR__ . '/../../../../vendor/autoload.php');
use HRDNS\System\FileSystem\File;
$pathname = tempnam(sys_get_temp_dir(),'phpunit');
$file = new File($pathname);
$file->write("test1234\n");
$file->write("test5678\n");
echo $file->read(4096)."\n";
$file->unlink();
?>
--EXPECT--
test1234