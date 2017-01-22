<?php

namespace HRDNS\Tests\Protocol;

use HRDNS\Protocol\FTP;
use HRDNS\Exception\IOException;

class FTPTest extends \PHPUnit_Framework_TestCase
{

    /** @var FTP */
    private $ftp;

    public function setUp()
    {
        $this->ftp = new FTP();
    }

    public function testHost()
    {
        $this->assertEquals('host',$this->ftp->setHost('host')->getHost());
    }

    public function testPort()
    {
        $this->assertEquals(123,$this->ftp->setPort(123)->getPort());
    }

    public function testUser()
    {
        $this->assertEquals('user',$this->ftp->setUser('user')->getUser());
    }

    public function testSSL()
    {
        $this->assertTrue($this->ftp->setSsl(true)->isSsl());
        $this->assertFalse($this->ftp->setSsl(false)->isSsl());
    }

    public function testTimeout()
    {
        $this->assertEquals(10,$this->ftp->setTimeout(10)->getTimeout());
    }

    public function testInvalidTimeout()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->ftp->setTimeout(0);
    }

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
