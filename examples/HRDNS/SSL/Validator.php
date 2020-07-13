<?php

namespace HRDNS\Examples\SSL;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use HRDNS\SSL\Validator as SSLValidator;

class Validator extends Command
{

    const DEFAULT_HOST = '127.0.0.1';
    const DEFAULT_PORT = 443;
    const COMMAND_NAME = 'example:sslvalidator';

    /** @var string */
    private $host = null;

    /** @var int */
    private $port = null;

    protected function configure()
    {
        $this->setName(self::COMMAND_NAME);
        $this->setProcessTitle($this->getName());
        $this->setDescription('A TCPServer EchoServer');
        $this->setHelp('');
        $this->addOption(
            'host',
            'H',
            InputOption::VALUE_OPTIONAL,
            '',
            self::DEFAULT_HOST
        );
        $this->addOption(
            'port',
            'P',
            InputOption::VALUE_OPTIONAL,
            '',
            self::DEFAULT_PORT
        );
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $this->host = (string)$input->getOption('host');
        $this->port = (int)$input->getOption('port');

        if ($this->host == self::DEFAULT_HOST && $this->port == self::DEFAULT_PORT) {

            /** @var \Symfony\Component\Console\Helper\QuestionHelper $questionHelper */
            $questionHelper = $this->getHelper('question');

            $questionHost = new Question(
                sprintf('<info>Host <comment>[%s]<comment>:</info>', $this->host),
                $this->host
            );

            $questionPort = new Question(
                sprintf('<info>Port <comment>[%s]<comment>:</info>', $this->port),
                $this->port
            );

            $this->host = $questionHelper->ask($input, $output, $questionHost);
            $this->port = $questionHelper->ask($input, $output, $questionPort);

            $output->writeln('');
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!filter_var($this->host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $host = gethostbyname($this->host);
            if ($host == $this->host) {
                throw new \InvalidArgumentException('Invalid listen address.');
            }
            $this->host = $host;
        }

        if ($this->port < 1 || $this->port > 65535) {
            throw new \InvalidArgumentException('Invalid port number.');
        }

        $result = array();
        $validator = new SSLValidator();
        $protocols = $validator->getProtocols();
        $ciphers = $validator->getCiphers();

        $length = 0;
        array_walk(
            $protocols,
            function (&$value) use (&$length) {
                $length = max($length, strlen($value));
            }
        );
        array_walk(
            $ciphers,
            function (&$value) use (&$length) {
                $length = max($length, strlen($value));
            }
        );
        $length += 7;

        $progress = new ProgressBar($output, count($ciphers) * count($protocols));
        #$progress->setFormat('verbose');
        $progress->start();
        foreach ($protocols as $protocol) {
            if (!isset($result[$protocol])) {
                $result[$protocol] = array();
            }
            foreach ($ciphers as $cipher) {
                $progress->setMessage(
                    sprintf(
                        '%s %s',
                        str_replace('_', '.', strtoupper($protocol)),
                        strtoupper($cipher)
                    )
                );
                if ($validator->verifySingle($this->host, $this->port, $protocol, $cipher)) {
                    $result[$protocol][] = $cipher;
                }
                ob_start();
                $progress->advance();
                $output->write(
                    ' ' .
                    str_pad(
                        $progress->getMessage(),
                        $length,
                        ' '
                    )
                );
                $output->write("\r");
                ob_end_clean();
            }
        }
        $progress->finish();

        $output->write("\n\n");

        foreach ($result as $protocol => &$ciphers) {
            foreach ($ciphers as $cipher) {
                $output->writeln(
                    sprintf(
                        '<info>%s</info> :: <comment>%s</comment>',
                        str_replace('_', '.', strtoupper($protocol)),
                        strtoupper($cipher)
                    )
                );
            }
        }

        return 0;
    }

}
