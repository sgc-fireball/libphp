<?php

namespace HRDNS\Examples\Socket;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Command\Command;
use HRDNS\Socket\Server\ServerClient;
use HRDNS\Socket\Server\WebSocket as WebSocketBase;

class WebSocket extends WebSocketBase
{

    /** @var OutputInterface */
    private $output = null;

    public function setOutputHandler(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @param ServerClient $client
     * @return void
     */
    public function onWebSocketConnect(ServerClient $client)
    {
        $this->output->writeln(
            sprintf(
                '<info>%s</info> :: <comment>%s</comment>',
                $client->getId(),
                __METHOD__
            )
        );
    }

    /**
     * @param ServerClient $client
     * @param string $buffer
     * @return void
     */
    public function onWebSocketIncoming(ServerClient $client, string $buffer)
    {
        $this->output->writeln(
            sprintf(
                '<info>%s</info> :: <comment>%s</comment> :: %s',
                $client->getId(),
                __METHOD__,
                $buffer
            )
        );
        /*$this->sendWebSocket(
            $client,
            sprintf(
                'PONG: %s',
                $buffer
            )
        );*/
    }

    /**
     * @param ServerClient $client
     * @return void
     */
    public function onWebSocketTick(ServerClient $client)
    {
        $this->output->writeln(
            sprintf(
                '<info>%s</info> :: <comment>%s</comment>',
                $client->getId(),
                __METHOD__
            )
        );
        $this->sendWebSocket($client,microtime(true));
    }

    /**
     * @param ServerClient $client
     * @param string $buffer
     * @return void
     */
    public function onWebSocketOutgoing(ServerClient $client, string $buffer)
    {
        $this->output->writeln(
            sprintf(
                '<info>%s</info> :: <comment>%s</comment> :: %s',
                $client->getId(),
                __METHOD__,
                $buffer
            )
        );
    }

    /**
     * @param ServerClient $client
     * @return void
     */
    public function onWebSocketDisconnect(ServerClient $client)
    {
        $this->output->writeln(
            sprintf(
                '<info>%s</info> :: <comment>%s</comment>',
                $client->getId(),
                __METHOD__
            )
        );
    }

}

class WebSocketServer extends Command
{

    const DEFAULT_HOST = '127.0.0.1';
    const DEFAULT_PORT = 8080;
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
        $this->setDescription('A WebSocket Echo Server');
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
                $this->host,
                '/^[a-z0-9\.-]$/'
            );

            $questionPort = new Question(
                sprintf('<info>Port <comment>[%s]<comment>:</info>', $this->port),
                $this->port,
                '/^[0-9]{1,5}$/'
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

        if ($this->runtime < 0 || $this->runtime > 300) {
            throw new \InvalidArgumentException('Invalid runtime value.');
        }

        $output->writeln(sprintf("Bind WebSocketServer to <info>%s:%d</info>\n", $this->host, $this->port));

        $server = new WebSocket();
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