<?php

namespace HRDNS\Examples\Socket;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Command\Command;
use HRDNS\Socket\Client\SimpleServiceDiscoveryProtocolClient;
use HRDNS\Socket\Server\SimpleServiceDiscoveryProtocolServer;
use HRDNS\Socket\Client\SimpleServiceDiscoveryProtocol\EventDiscover;
use HRDNS\Socket\Client\SimpleServiceDiscoveryProtocol\SsdpResponse;

class SimpleServiceDiscoveryProtocolClientCommand extends Command
{

    /** @var integer */
    private $discoverTime = 60;

    protected function configure()
    {
        $this->setName('exmaple:' . strtolower(str_replace('.php', '', basename(__FILE__))));
        $this->setProcessTitle($this->getName());
        $this->setDescription('A SSDP client to find UPNP devices.');
        $this->setHelp('');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return integer
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var SsdpResponse[] $services */
        $services = [];

        $server = new SimpleServiceDiscoveryProtocolServer();
        $server->addAllowedMulticastAddress('239.255.255.250');
        $server->bind();

        $client = new SimpleServiceDiscoveryProtocolClient($this->discoverTime);
        $client->setServer($server);
        $client->addEvent(
            EventDiscover::EVENT_NAME,
            function (EventDiscover $event) use (&$services) {
                $service = $event->getSsdpResponse();
                if (isset($services[$service->getLocation()])) {
                    return;
                }
                $services[$service->getLocation()] = $service;
                printf("%s\n\t<%s>\n\n", $service->getServer(), $service->getLocation());
            }
        );

        $client->discover($this->discoverTime);

        return 0;
    }

}
