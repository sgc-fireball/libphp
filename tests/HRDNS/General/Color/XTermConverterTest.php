<?php

namespace HRDNS\Tests\General\Color;

use HRDNS\General\Color\XTermConverter;

class XTermConverterTest extends \PHPUnit_Framework_TestCase
{

    /** @var XTermConverter */
    private $converter = null;

    public function setUp()
    {
        parent::setUp();
        $this->converter = new XTermConverter();
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        $result = [];
        foreach (XTermConverter::getMap() as $hex => $xterm) {
            $result[] = [$hex, $xterm];
        }
        return $result;
    }

    /**
     * @dataProvider dataProvider
     */
    public function testXterm2Hex($hex, $xterm)
    {
        $this->assertEquals($this->converter->xterm2hex($xterm), $hex);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testHex2Xterm($hex, $xterm)
    {
        $this->assertEquals($this->converter->hex2xterm($hex), $xterm);
    }

    public function test88ffff()
    {
        $this->assertEquals($this->converter->hex2xterm('88ffff'),123);
    }

}
