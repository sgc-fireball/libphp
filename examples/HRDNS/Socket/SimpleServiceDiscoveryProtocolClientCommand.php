<?php

namespace HRDNS\Examples\Socket;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Command\Command;
use HRDNS\Socket\Client\SimpleServiceDiscoveryProtocolClient;
use HRDNS\Socket\Client\SimpleServiceDiscoveryProtocol\EventDiscover;

class SimpleServiceDiscoveryProtocolClientCommand extends Command
{

    /** @var integer */
    private $discoverTime = 10;

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
        $ssdpClient = new SimpleServiceDiscoveryProtocolClient($this->discoverTime);
        $ssdpClient->addEvent(
            EventDiscover::EVENT_NAME,
            function(EventDiscover $event) {
                $data = $event->getSsdpResponse();
                printf("%s\n\t<%s>\n\n",$data->getServer(),$data->getLocation());
            }
        );
        $ssdpClient->discover($this->discoverTime);
        return 0;
    }

}
