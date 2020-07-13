<?php

namespace HRDNS\Examples\Socket;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Command\Command;
use HRDNS\Socket\Server\TCPServer;
use HRDNS\Socket\Server\ServerClient;

class Server extends TCPServer
{

    /** @var OutputInterface */
    protected $output = null;

    public function setOutputHandler(OutputInterface $output)
    {
        $this->output = $output;
        return $this;
    }

    public function onConnect(ServerClient $client)
    {
        $this->output->writeln(
            sprintf(
                '<info>Client[%s:%d]</info> :: <comment>%s</comment>',
                $client->getHost(),
                $client->getPort(),
                __METHOD__
            )
        );
    }

    public function onIncoming(ServerClient $client, string $buffer)
    {
        $buffer = trim($buffer);

        $this->output->writeln(
            sprintf(
                '<info>Client[%s:%d]</info> :: <comment>%s</comment> :: %s',
                $client->getHost(),
                $client->getPort(),
                __METHOD__,
                $buffer
            )
        );

        if (in_array(strtolower($buffer), array('exit', 'quit', 'bye', 'bye bye', 'byebye'))) {
            $this->onDisconnect($client);
            $this->disconnect($client);
            return;
        }
        $this->send($client, $buffer . "\n");
    }

    public function onOutgoing(ServerClient $client, string $buffer)
    {
        $buffer = trim($buffer);
        $this->output->writeln(
            sprintf(
                '<info>Client[%s:%d]</info> :: <comment>%s</comment> :: %s',
                $client->getHost(),
                $client->getPort(),
                __METHOD__,
                $buffer
            )
        );
    }

    public function onDisconnect(ServerClient $client)
    {
        $this->output->writeln(
            sprintf(
                '<info>Client[%s:%d]</info> :: <comment>%s</comment> :: connection closed',
                $client->getHost(),
                $client->getPort(),
                __METHOD__
            )
        );
    }

    public function onTick(ServerClient $client)
    {
        $time = (int)$client->getAttribute('tick');
        if ($time == time()) {
            return;
        }
        $client->setAttribute('tick',time());

        $this->output->writeln(
            sprintf(
                '<info>Client[%s:%d]</info> :: <comment>%s</comment>',
                $client->getHost(),
                $client->getPort(),
                __METHOD__
            )
        );
    }

}

class EchoServer extends Command
{

    const DEFAULT_HOST = '127.0.0.1';
    const DEFAULT_PORT = 54321;
    const DEFAULT_RUNTIME = 300;

    /** @var string */
    private $host = null;

    /** @var int */
    private $port = null;

    /** @var int */
    private $runtime = null;

    protected function configure()
    {
        $this->setName('example:' . strtolower(str_replace('.php', '', basename(__FILE__))));
        $this->setProcessTitle($this->getName());
        $this->setDescription('A TCPServer EchoServer');
        $this->setHelp('');
        $this->addOption(
            'listen',
            'L',
            InputOption::VALUE_OPTIONAL,
            'IPv4 address to listen the server.',
            self::DEFAULT_HOST
        );
        $this->addOption(
            'port',
            'P',
            InputOption::VALUE_OPTIONAL,
            'Port to bind the server. [1-65535]',
            self::DEFAULT_PORT
        );
        $this->addOption(
            'runtime',
            'R',
            InputOption::VALUE_OPTIONAL,
            'Time in seconds before exit. [0-300]',
            self::DEFAULT_RUNTIME
        );
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $this->host = (string)$input->getOption('listen');
        $this->port = (int)$input->getOption('port');
        $this->runtime = (int)$input->getOption('runtime');

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

            $this->host = $questionHelper->ask($input,$output,$questionHost);
            $this->port = $questionHelper->ask($input,$output,$questionPort);

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

        if ($this->runtime < 0 || $this->runtime > 300) {
            throw new \InvalidArgumentException('Invalid runtime value.');
        }

        $output->writeln(sprintf('Bind EchoServer to <info>%s:%d</info>', $this->host, $this->port));

        $server = new Server();
        $server->setOutputHandler($output);
        $server->setListen($this->host);
        $server->setPort($this->port);
        $server->bind();

        $endTime = time() + $this->runtime;
        while (time() < $endTime) {
            $server->listen(100);
        }

        return 0;
    }

}
