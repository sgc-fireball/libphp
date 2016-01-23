#!/usr/bin/env php
<?php

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

require_once(__DIR__ . DS . '..' . DS . 'examples' . DS . 'bootstrap.php');

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use HRDNS\Examples\SSL\Validator;

class Application extends BaseApplication
{

    const APP_NAME = 'SSL Validator';
    const APP_VERSION = '0.0.1';
    const AUTHOR_NAME = 'Richard Huelsberg';
    const AUTHOR_EMAIL = 'rh@hrdns.de';

    public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        parent::__construct(self::APP_NAME, self::APP_VERSION);
    }

    protected function getCommandName(InputInterface $input)
    {
        return 'exmaple:sslvalidator';
    }

    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();
        $commands[] = new Validator();
        return $commands;
    }

    public function getDefinition()
    {
        $inputDefinition = parent::getDefinition();
        $inputDefinition->setArguments();
        return $inputDefinition;
    }

    public function doRun(InputInterface $input, OutputInterface $output)
    {
        if (!$input->hasParameterOption('--quiet')) {
            $output->write(
                sprintf(
                    "<info>%s</info> <comment>v%s</comment> by <comment>%s</comment> <%s>\n\n",
                    self::APP_NAME,
                    $this->getVersion(),
                    self::AUTHOR_NAME,
                    self::AUTHOR_EMAIL
                )
            );
        }
        if ($input->hasParameterOption('--version') || $input->hasParameterOption('-V')) {
            return 0;
        }
        return parent::doRun($input, $output);
    }

}

exit((new Application())->run());
