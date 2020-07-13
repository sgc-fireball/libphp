<?php declare(strict_types=1);

namespace HRDNS\Tests\Protocol;

use HRDNS\SSL\Validator;

class ValidatorTest extends \PHPUnit\Framework\TestCase
{

    /** @var bool */
    static $skipped = false;

    /** @var Validator */
    private $validator;

    protected function setUp(): void
    {
        try {
            $this->validator = new Validator();
        } catch (\Exception $e) {
            self::$skipped = true;
        }
    }

    public function testProtocols()
    {
        $data = $this->validator->getProtocols();
        $this->assertTrue(is_array($data));
        $this->assertTrue(count($data) > 0);
    }

    public function testCiphers()
    {
        if (self::$skipped) {
            $this->markTestSkipped();
        }
        $data = $this->validator->getCiphers();
        $this->assertTrue(is_array($data));
        $this->assertTrue(count($data) > 0);
    }

    public function testValid()
    {
        if (self::$skipped) {
            $this->markTestSkipped();
        }
        $validator = $this->getMockBuilder(Validator::class)->onlyMethods(['verifySingle'])->getMock();
        $validator->method('verifySingle')->will($this->returnValue(true));

        $result = $validator->verify('domain.de', 80);
        $this->assertTrue(is_array($result));
        $this->assertTrue(array_key_exists('host', $result));
        $this->assertEquals('domain.de', $result['host']);
        $this->assertEquals(80, $result['port']);
        $this->assertTrue(array_key_exists('protocol', $result));
        $this->assertTrue(count($result['protocol']) > 0);
        $this->assertTrue(array_key_exists('results', $result));
        $this->assertTrue(count($result['results']) > 0);
    }

}
