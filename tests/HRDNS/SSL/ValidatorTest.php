<?php

namespace HRDNS\Tests\Protocol;

use HRDNS\SSL\Validator;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{

    /** @var bool */
    static $skipped = false;

    /** @var Validator */
    private $validator;

    protected function setUp()
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

}
