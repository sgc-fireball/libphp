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

    public function testInvalidHost()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->ftp->setHost('');
    }

    public function testPort()
    {
        $this->assertEquals(123,$this->ftp->setPort(123)->getPort());
    }

    public function testInvalidPortLow()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->ftp->setPort(0);
    }

    public function testInvalidPortHigh()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->ftp->setPort(65536);
    }

    public function testUser()
    {
        $this->assertEquals('user',$this->ftp->setUser('user')->getUser());
    }

    public function testPassword()
    {
        $this->assertEquals('pass',$this->ftp->setPassword('pass')->getPassword());
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

    public function testInvalidConnect()
    {
        $this->expectException(IOException::class);
        $ftp = new FTP();
        $ftp->setHost('127.0.0.1');
        $ftp->setPort(1);
        $ftp->connect();
    }

    public function testInvalidLogin()
    {
        $ftp = new FTP();
        $ftp->setHost('127.0.0.1');
        $ftp->setPort(1);
        $ftp->setUser('');
        $ftp->setPassword('');
        $this->assertEquals(FTP::class,get_class($ftp->login()));
        $ftp->setUser('anonymous');
        $ftp->setPassword('anonymous@anonymous');
        $this->expectException(IOException::class);
        $ftp->login();
    }

    public function testInvalidPassiv()
    {
        $this->expectException(IOException::class);
        $ftp = new FTP();
        $ftp->passiv();
    }

    public function testInvalidDir()
    {
        $this->expectException(IOException::class);
        $ftp = new FTP();
        $ftp->dir('.');
    }

    public function testInvalidCd()
    {
        $this->expectException(IOException::class);
        $ftp = new FTP();
        $ftp->cd('.');
    }

    public function testInvalidPwd()
    {
        $this->expectException(IOException::class);
        $ftp = new FTP();
        $ftp->pwd();
    }

    public function testInvalidChmod()
    {
        $this->expectException(IOException::class);
        $ftp = new FTP();
        $ftp->chmod(1,'a');
    }

    public function testInvalidRm()
    {
        $this->expectException(IOException::class);
        $ftp = new FTP();
        $ftp->rm('a');
    }

    public function testInvalidMkdir()
    {
        $this->expectException(IOException::class);
        $ftp = new FTP();
        $ftp->mkdir('a');
    }

    public function testInvalidRmdir()
    {
        $this->expectException(IOException::class);
        $ftp = new FTP();
        $ftp->rmdir('a');
    }

    public function testInvalidSize()
    {
        $this->expectException(IOException::class);
        $ftp = new FTP();
        $ftp->size('a');
    }

    public function testInvalidModifiedTime()
    {
        $this->expectException(IOException::class);
        $ftp = new FTP();
        $ftp->modifiedTime('a');
    }

    public function testInvalidGet()
    {
        $this->expectException(IOException::class);
        $ftp = new FTP();
        $ftp->get('foo','bar');
    }

    public function testInvalidPut1()
    {
        $this->expectException(\RuntimeException::class);
        $ftp = new FTP();
        $ftp->put('foo','bar');
    }

    public function testInvalidPut2()
    {
        $this->expectException(IOException::class);
        $ftp = new FTP();
        $ftp->put('/dev/null','bar');
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
