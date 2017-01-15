<?php

namespace HRDNS\Examples\System\Network;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Command\Command;
use HRDNS\System\Network\RIPE\Database as RipeDatabase;
use HRDNS\Types\IPv4;
use HRDNS\Types\IPv6;

class HtaccessBuilderCommand extends Command
{

    /** @var string */
    private $file = '';

    /** @var string */
    private $file22 = '';

    /** @var string */
    private $file24 = '';

    private $countires = ['de'];

    protected function configure()
    {
        $this->setName('exmaple:htaccess:builder');
        $this->setProcessTitle($this->getName());
        $this->setDescription('A htaccess builder command.');
        $this->setHelp('');

        $this->addArgument('output', InputArgument::REQUIRED, 'filepath');
        $this->addArgument('countries', InputArgument::OPTIONAL, 'Comma separated list of country codes','de');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return integer
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $self = $this;
        $ripe = new RipeDatabase(
            function (IPv4 $ipv4, string $countryCode, int $since, array $data) use ($self) {
                $self->handleIpv4($ipv4, $countryCode, $since, $data);
            },
            function (IPv6 $ipv6, string $countryCode, int $since, array $data) use ($self) {
                $self->handleIpv6($ipv6, $countryCode, $since, $data);
            },
            function (string $asNumber, string $countryCode, int $since, array $data) use ($self) {
                $self->handleAsn($asNumber, $countryCode, $since, $data);
            }
        );

        $this->countires = explode(',',strtolower($input->getArgument('countries')));

        $this->file .= "SetEnvIf User-Agent .*googlebot.* search_robot\n";
        $this->file .= "SetEnvIf User-Agent .*bingbot.* search_robot\n";

        $this->file22 .= "<IfModule !mod_authz_core.c>\n";
        $this->file22 .= "    Order deny,allow\n";
        $this->file22 .= "    Allow from env=search_robot\n";

        $this->file24 .= "<IfModule mod_authz_core.c>\n";
        $this->file24 .= "    Require all denied\n";
        $this->file24 .= "    Require env search_robot\n";

        $tmpName = sprintf('%s/ripe_%s.csv',sys_get_temp_dir(),date('Ymd'));
        if (!file_exists($tmpName)) {
            $ripe->download($tmpName);
            $ripe->decompress($tmpName);
        }
        $ripe->convert($tmpName);

        $this->file22 .= "</IfModule>\n";
        $this->file24 .= "</IfModule>\n";

        $file = $input->getArgument('output');
        file_put_contents($file,$this->file);
        file_put_contents($file,$this->file22,FILE_APPEND);
        file_put_contents($file,$this->file24,FILE_APPEND);
        return 0;
    }

    /**
     * @param IPv4 $ipv4
     * @param string $countryCode
     * @param int $since
     * @param array $data
     * [0] => ripencc
     * [1] => FR
     * [2] => ipv4
     * [3] => 2.0.0.0
     * [4] => 1048576
     * [5] => 20100712
     * [6] => allocated
     * [7] => a1e33a7d-5964-4bd7-ae72-980c57b0cf72
     */
    public function handleIpv4(IPv4 $ipv4, string $countryCode, int $since, array $data)
    {
        $countryCode = strtolower($countryCode);
        if (!in_array($countryCode,$this->countires)) {
            return;
        }
        $this->file22 .= sprintf("    Allow from %s/%d # %s\n",$ipv4->getNetmask(),$ipv4->getCIDR(),$countryCode);
        $this->file24 .= sprintf("    Require ip %s/%d # %s\n",$ipv4->getNetmask(),$ipv4->getCIDR(),$countryCode);
    }

    /**
     * @param IPv6 $ipv6
     * @param string $countryCode
     * @param int $since
     * @param array $data
     * [0] => ripencc
     * [1] => NL
     * [2] => ipv6
     * [3] => 2001:67c:26ac::
     * [4] => 48
     * [5] => 20120203
     * [6] => assigned
     * [7] => 39530da4-b33b-4077-a077-06bf85f3f17e
     */
    public function handleIpv6(IPv6 $ipv6, string $countryCode, int $since, array $data)
    {
        $countryCode = strtolower($countryCode);
        if (!in_array($countryCode,$this->countires)) {
            return;
        }
        $this->file22 .= sprintf("    Allow from %s/%d # %s\n",$ipv6->getNetmask(),$ipv6->getCIDR(),$countryCode);
        $this->file24 .= sprintf("    Require ip %s/%d # %s\n",$ipv6->getNetmask(),$ipv6->getCIDR(),$countryCode);
    }

    /**
     * @param string $asNumber
     * @param string $countryCode
     * @param int $since
     * @param array $data
     * [0] => ripencc
     * [1] => DE
     * [2] => asn
     * [3] => 28
     * [4] => 1
     * [5] => 19930901
     * [6] => allocated
     * [7] => 2053ae4a-520c-44ab-bbdb-c4e751e3c4f6
     */
    public function handleAsn(string $asNumber, string $countryCode, int $since, array $data)
    {

    }

}
