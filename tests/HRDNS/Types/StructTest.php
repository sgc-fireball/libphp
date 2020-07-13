<?php declare(strict_types=1);

namespace Tests\HRDNS\Types;

use HRDNS\Types\Struct;

class StructTest extends \PHPUnit\Framework\TestCase
{

    public function testConstruct()
    {
        $struct = new Struct(['test' => 'test']);
        $this->assertEquals('test', $struct->test);
    }

    public function testExports()
    {
        $struct = new Struct(
            array(
                'test' => 'test'
            )
        );
        $this->assertEquals('{"test":"test"}', $struct->getJSON());
        $this->assertEquals(['test' => 'test'], $struct->getArray());
        $this->assertEquals('a:1:{s:4:"test";s:4:"test";}', $struct->getSerialize());
        $this->assertEquals('<?xml version="1.0" encoding="UTF-8" ?>
<struct type="object" class="HRDNS\Types\Struct">
    <test type="string"><![CDATA[test]]></test>
</struct>
', $struct->getXML());
    }

    public function testLoadFromJson()
    {
        $struct = new Struct();
        $struct->loadFromJSON('{"test":"test"}');
        $this->assertEquals('test', $struct->test);
    }

    public function testLoadFromSerialize()
    {
        $struct = new Struct();
        $struct->loadFromSerialize('a:1:{s:4:"test";s:4:"test";}');
        $this->assertEquals('test', $struct->test);
    }

    public function testStructInStruct()
    {
        $structA = new Struct(
            array(
                'testa' => new Struct(
                    array(
                        'testb' => 'testB'
                    )
                )
            )
        );
        $this->assertEquals('<?xml version="1.0" encoding="UTF-8" ?>
<struct type="object" class="HRDNS\Types\Struct">
    <testa type="object" class="HRDNS\Types\Struct">
        <testb type="string"><![CDATA[testB]]></testb>
    </testa>
</struct>
', $structA->getXML());
    }

}
