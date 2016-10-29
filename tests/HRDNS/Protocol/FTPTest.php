<?php

namespace Test\HRDNS\Protocol;

use HRDNS\Protocol\FTP;
use HRDNS\Exception\IOException;

class FTPTest extends \PHPUnit_Framework_TestCase
{

    public function testRipeFtpServer()
    {
        $oDateTime = new \DateTime('yesterday');

        try {
            $ftp = (new FTP())
                ->setHost('ftp.ripe.net')
                ->setPort(21)
                ->setUser('anonymous')
                ->setPassword('anonymous@anonymous')
                ->connect()
                ->login()
                ->passiv()
                ->cd('/ripe/stats/' . $oDateTime->format('Y') . '/');
            $this->assertGreaterThan(0, count($ftp->dir()));
            $ftp->disconnect();
        } catch (IOException $e) {
            if (strpos($e,'Could not connect to')===0) {
                $this->markTestSkipped($e->getMessage());
            }
            throw $e;
        }
    }

}
