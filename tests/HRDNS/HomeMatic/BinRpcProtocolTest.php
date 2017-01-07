<?php

namespace Test\HRDNS\HomeMatic;

use HRDNS\HomeMatic\BinRpcProtocol;

class BinRpcProtocolTest extends \PHPUnit_Framework_TestCase
{

    /** @var BinRpcProtocol */
    private $protocol = null;

    public function setUp()
    {
        $this->protocol = new BinRpcProtocol();
    }

    public function testRequest()
    {
        $method = 'TestFunction123';
        $plain = (string)$this->protocol->encodeRequest($method,[]);
        $data = $this->protocol->decodeRequest($plain);
        $this->assertTrue(is_array($data));
        $this->assertTrue(array_key_exists('type', $data));
        $this->assertEquals('request', $data['type']);
        $this->assertTrue(array_key_exists('method', $data));
        $this->assertEquals($method, $data['method']);
        $this->assertTrue(array_key_exists('params', $data));
        $this->assertTrue(is_array($data['params']));
    }

    public function testResponse()
    {
        $plain = (string)$this->protocol->encodeResponse([]);
        $data = $this->protocol->decodeResponse($plain);
        $this->assertTrue(is_array($data));
        $this->assertTrue(array_key_exists('type', $data));
        $this->assertEquals('response', $data['type']);
        $this->assertTrue(array_key_exists('method', $data));
        $this->assertEquals('unknown', $data['method']);
        $this->assertTrue(array_key_exists('params', $data));
        $this->assertTrue(is_array($data['params']));
    }

    public function providerTestInteger()
    {
        $min = pow(2,31) * -1;
        $max = pow(2,31) - 1;
        return [
            [$min],
            [$min+1],
            [mt_rand($min, 0)],
            [0],
            [mt_rand(0, $max)],
            [$max-1],
            [$max]
        ];
    }

    /**
     * @dataProvider providerTestInteger
     */
    public function testInteger(int $int)
    {
        try {
            $plain = (string)$this->protocol->encodeResponse(['int' => $int]);
            $data = $this->protocol->decodeResponse($plain);
            $this->assertTrue(is_array($data));
            $this->assertTrue(is_array($data['params']));
            $this->assertTrue(array_key_exists('int', $data['params']));
            $this->assertEquals($int, $data['params']['int']);
            $this->assertTrue( $data['params']['int'] === $int);
        } catch (\Exception $e) {
            $this->markTestSkipped(__METHOD__.' '.$e->getMessage());
        }
    }

    public function providerTestBoolean()
    {
        return [
            ['A'],
            [0],
            [1],
            [true],
            [false]
        ];
    }

    /**
     * @dataProvider providerTestBoolean
     */
    public function testBool(bool $bool)
    {
        $plain = (string)$this->protocol->encodeResponse(['bool' => $bool]);
        $data = $this->protocol->decodeResponse($plain);
        $this->assertTrue(is_array($data));
        $this->assertTrue(array_key_exists('params', $data));
        $this->assertTrue(is_array($data['params']));
        $this->assertTrue(array_key_exists('bool', $data['params']));
        $this->assertEquals((bool)$bool, $data['params']['bool']);
        $this->assertTrue( $data['params']['bool'] === (bool)$bool);
    }

    public function testString()
    {
        $plain = (string)$this->protocol->encodeResponse(['string' => 'tasdasdasd']);
        $data = $this->protocol->decodeResponse($plain);
        $this->assertTrue(is_array($data));
        $this->assertTrue(array_key_exists('params', $data));
        $this->assertTrue(is_array($data['params']));
        $this->assertTrue(array_key_exists('string', $data['params']));
        $this->assertEquals('tasdasdasd', $data['params']['string']);
    }

    public function testStruct()
    {
        $plain = (string)$this->protocol->encodeResponse(['foo'=>['bar'=>1337]]);
        $data = $this->protocol->decodeResponse($plain);
        $this->assertTrue(is_array($data));
        $this->assertTrue(array_key_exists('params', $data));
        $this->assertTrue(is_array($data['params']));
        $this->assertFalse(array_key_exists(0, $data['params']));
        $this->assertTrue(array_key_exists('foo', $data['params']));
        $this->assertTrue(is_array($data['params']['foo']));
        $this->assertFalse(array_key_exists(0, $data['params']['foo']));
        $this->assertTrue(array_key_exists('bar', $data['params']['foo']));
        $this->assertEquals(1337, $data['params']['foo']['bar']);
        $this->assertTrue(1337 === $data['params']['foo']['bar']);
    }

    public function testArray()
    {
        $plain = (string)$this->protocol->encodeResponse([1,2,3]);
        $data = $this->protocol->decodeResponse($plain);
        $this->assertTrue(is_array($data));
        $this->assertTrue(array_key_exists('params', $data));
        $this->assertTrue(is_array($data['params']));
        $this->assertTrue(array_key_exists(0, $data['params']));
        $this->assertEquals(1,$data['params'][0]);
        $this->assertTrue(array_key_exists(1, $data['params']));
        $this->assertEquals(2,$data['params'][1]);
        $this->assertTrue(array_key_exists(2, $data['params']));
        $this->assertEquals(3,$data['params'][2]);
        $this->assertFalse(array_key_exists(3, $data['params']));
        $this->assertFalse(array_key_exists('foo', $data['params']));
    }

    public function providerTestFloat()
    {
        $min = pow(2,31) * -1;
        $max = pow(2,31) - 1;
        return [
            [$min],
            [$min+1],
            [mt_rand($min, -1)],
            [-1234.5678],
            [-1],
            [0],
            [1],
            [1234.5678],
            [mt_rand(1, $max)],
            [$max-1],
            [$max],
            [3.402823 * pow(10,37)],
            [-3.402823 * pow(10,37)]
        ];
    }

    /**
     * @dataProvider providerTestFloat
     */
    public function testFloat(float $float)
    {
        try {
            $plain = (string)$this->protocol->encodeResponse(['float' => (float)$float]);
            $data = $this->protocol->decodeResponse($plain);
            $this->assertTrue(is_array($data));
            $this->assertTrue(array_key_exists('params', $data));
            $this->assertTrue(is_array($data['params']));
            $this->assertTrue(array_key_exists('float', $data['params']));

            $min = $float * 0.999999999;
            $max = $float * 1.000000001;
            if ($float < 0) {
                $tmp = $min;
                $min = $max;
                $max = $tmp;
            }

            $this->assertTrue($min <= $data['params']['float'] && $data['params']['float'] <= $max);
        } catch (\Exception $e) {
            $this->markTestSkipped(__METHOD__.' '.$e->getMessage());
        }
    }

}
