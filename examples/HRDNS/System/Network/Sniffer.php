<?php

namespace HRDNS\Examples\System\Network;

use Symfony\Component\Console\Command\Command;
use HRDNS\System\Network\Sniffer as NetworkSniffer;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;

class Sniffer extends Command
{

    protected function configure()
    {
        $this->setName('example:' . strtolower(str_replace('.php', '', basename(__FILE__))));
        $this->setProcessTitle($this->getName());
        $this->setDescription('A PHP incoming network sniffer.');
        $this->setHelp('');
        $this->addOption('device','d',InputOption::VALUE_OPTIONAL,'listen interface','lo');
        $this->addOption('time','t',InputOption::VALUE_OPTIONAL,'time to sniff in sec.',-1);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $allow = preg_quote(",;.:-_!\"$%&/()=?*[]{}\n ");
        $sniffer = new NetworkSniffer(
            function($packet)use($allow){
                static $hash;
                $hash = isset($hash) ? $hash : false;

                if (!$packet['data']) {
                    echo '#';
                    $hash = true;
                    return;
                }

                if ($hash) {
                    echo "\n";
                    $hash = false;
                }

                $hash = false;
                $type = '?';
                $type = $packet['ipProtocol'] == 1 ? 'I' : $type;
                $type = $packet['ipProtocol'] == 6 ? 'T' : $type;
                $type = $packet['ipProtocol'] == 17 ? 'U' : $type;

                if ($type=='T' || $type=='U') {
                    $spt = isset($packet['tcpSourcePort']) ? $packet['tcpSourcePort'] : $packet['udpSourcePort'];
                    $dpt = isset($packet['tcpDestinationPort']) ? $packet['tcpDestinationPort'] : $packet['udpDestinationPort'];
                    if ($spt == 22 || $dpt == 22 ) {
                        return;
                    }
                    printf("%s %s:%d -> %s:%d\n%s\n",
                        $type,
                        $packet['ipSource'],
                        $spt,
                        $packet['ipDestination'],
                        $dpt,
                        preg_replace('#[^a-zA-Z0-9'.$allow.']#', '.', $packet['data'])
                    );
                } else if ($type == 'I') {
                    printf(
                        "%s %s -> %s (Code: %d Type: %d)\n%s\n",
                        $type,
                        $packet['ipSource'],
                        $packet['ipDestination'],
                        $packet['icmpType'],
                        $packet['icmpCode'],
                        preg_replace('#[^a-zA-Z0-9'.$allow.']#', '.', $packet['data'])
                    );
                }
            },
            $input->getOption('device')
        );

        $durotation = $input->getOption('time') == -1 ? PHP_INT_MAX : (int)$input->getOption('time');
        $endTime = time() + $durotation;
        while (time() < $endTime) {
            $sniffer->listen(100);
        }
        return 0;
    }

}
