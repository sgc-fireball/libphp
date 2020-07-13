<?php declare(strict_types=1);

namespace HRDNS\Tests\General\Color;

use HRDNS\General\Color\XTermConverter;

class XTermConverterTest extends \PHPUnit\Framework\TestCase
{

    /** @var XTermConverter */
    private $converter = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->converter = new XTermConverter();
    }

    public function testMap()
    {
        $map = XTermConverter::getMap();
        $this->assertTrue(is_array($map));
        $this->assertTrue(count($map) > 0);
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        $result = [];
        foreach (XTermConverter::getMap() as $hex => $xterm) {
            $result[] = [(string)$hex, $xterm];
        }
        return $result;
    }

    /**
     * @dataProvider dataProvider
     */
    public function testXterm2Hex(string $hex, int $xterm)
    {
        $this->assertEquals($hex, $this->converter->xterm2hex($xterm));
    }

    /**
     * @dataProvider dataProvider
     */
    public function testHex2Xterm(string $hex, int $xterm)
    {
        $this->assertEquals($xterm, $this->converter->hex2xterm($hex));
    }

    public function test88ffff()
    {
        $this->assertEquals(123, $this->converter->hex2xterm('88ffff'));
    }

    public function dataXterm2HexProvider()
    {
        $result = [];
        $result[] = ['000000', 16];
        $result[] = ['0000ff', 21];
        $result[] = ['00ff00', 46];
        $result[] = ['00ffff', 51];
        $result[] = ['ff0000', 196];
        $result[] = ['ff00ff', 201];
        $result[] = ['ffff00', 226];
        $result[] = ['ffffff', 231];
        $result[] = ['808080', 244];
        return $result;
    }

    /**
     * @dataProvider dataXterm2HexProvider
     * @param string $hex
     * @param int $xterm
     */
    public function testXterm2Hex2(string $hex, int $xterm)
    {
        $this->assertEquals($hex, $this->converter->xterm2hex($xterm));
    }

}
