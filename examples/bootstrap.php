<?php

set_time_limit(0);
error_reporting(E_ALL | E_STRICT | E_DEPRECATED);
ini_set('memory_limit',-1);

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

$path = __DIR__;
$loaded = false;
while ($path != DS && !empty($path)) {
    $loaders = array ($path . DS . 'autoload.php', $path . DS . 'vendor' . DS . 'autoload.php');
    foreach ($loaders as $loader) {
        if (file_exists($loader)) {
            require_once($loader);
            $loaded = true;
            break 2;
        }
    }
    $path = dirname($path);
}
if (!$loaded) {
    $msg = 'You need to set up the project dependencies using the following commands:' . PHP_EOL;
    $msg .= 'wget http://getcomposer.org/composer.phar' . PHP_EOL;
    $msg .= 'php composer.phar install' . PHP_EOL;
    fwrite(STDERR, $msg);
    exit(1);
}

