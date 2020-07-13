#!/usr/bin/env php
<?php

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

declare(ticks = 1000);

require_once(__DIR__ . DS . '..' . DS . 'examples' . DS . 'bootstrap.php');

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use HRDNS\Examples\Socket\EchoServer;
use HRDNS\Examples\Socket\WebSocketServer;
use HRDNS\Examples\Socket\SimpleServiceDiscoveryProtocolClientCommand;
use HRDNS\Examples\System\Network\Sniffer;
use HRDNS\Examples\System\Network\HtaccessBuilderCommand;

class Application extends BaseApplication
{

    const APP_NAME = 'HRDNS Examples';
    const APP_VERSION = '0.0.1';
    const AUTHOR_NAME = 'Richard Huelsberg';
    const AUTHOR_EMAIL = 'rh@hrdns.de';

    public function __construct()
    {
        parent::__construct(self::APP_NAME, self::APP_VERSION);
    }

    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();
        $commands[] = new EchoServer();
        $commands[] = new WebSocketServer();
        $commands[] = new SimpleServiceDiscoveryProtocolClientCommand();
        $commands[] = new Sniffer();
        $commands[] = new HtaccessBuilderCommand();
        return $commands;
    }

    public function doRun(InputInterface $input, OutputInterface $output)
    {
        if (!$input->hasParameterOption('--quiet')) {
            $output->write(
                sprintf(
                    "<info>%s</info> by <comment>%s</comment> <%s>\n\n",
                    self::APP_NAME,
                    self::AUTHOR_NAME,
                    self::AUTHOR_EMAIL
                )
            );
        }
        if ($input->hasParameterOption('--version') || $input->hasParameterOption('-V')) {
            return 0;
        }
        if (!$input->getFirstArgument()) {
            $input = new ArrayInput(
                ['list']
            );
        }
        return parent::doRun($input, $output);
    }

}

exit((new Application())->run());
