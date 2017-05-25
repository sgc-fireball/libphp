<?php

namespace HRDNS\Tests\HomeMatic;

use HRDNS\HomeMatic\BinRpcProtocol;

class BinRpcProtocolTest extends \PHPUnit_Framework_TestCase
{

    /** @var BinRpcProtocol */
    private $protocol = null;

    public function setUp()
    {
        $this->protocol = new BinRpcProtocol();
    }

    public function testInvalidDataDecodeShortData()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->protocol->decode('FOO');
    }

    public function testInvalidDataDecodePrefix()
    {
        $this->expectException(\InvalidArgumentException::class);
        $data = pack('A3CA*', BinRpcProtocol::PREFIX, 0xfffe, 'FOOBAR');
        $this->protocol->decode($data);
    }

    public function testInvalidDataDecodeType()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->protocol->decode('BINFOOBARRRRRRRRRR');
    }

    public function testInvalidDataDecodeRequestShortData()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->protocol->decodeRequest('FOO');
    }

    public function testInvalidDataDecodeRequestPrefix()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->protocol->decodeRequest('FOOBARRRRRRRRRRRRRRR');
    }

    public function testInvalidDataDecodeRequestType()
    {
        $this->expectException(\InvalidArgumentException::class);
        $data = (string)$this->protocol->encodeResponse([]);
        $this->protocol->decodeRequest($data);
    }

    public function testInvalidDataDecodeResponseShortData()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->protocol->decodeResponse('FOO');
    }

    public function testInvalidDataDecodeResponsePrefix()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->protocol->decodeResponse('FOOBARRRRRRRRRRRRRRR');
    }

    public function testInvalidDataDecodeResponseType()
    {
        $this->expectException(\InvalidArgumentException::class);
        $data = (string)$this->protocol->encodeRequest('function', []);
        $this->protocol->decodeResponse($data);
    }

    public function testInvalidDataDecode()
    {
        $this->expectException(\InvalidArgumentException::class);
        $data = $this->protocol->encodeResponse(['test' => 'test']);
        $data = substr($data, 0, -10).'AAAAAAAAAA';
        $this->protocol->decodeResponse($data);
    }

    public function testRequest()
    {
        $method = 'TestFunction123';
        $plain = (string)$this->protocol->encodeRequest($method, []);
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

    public function testUnknownMessage()
    {
        $method = 'TestFunction123';
        $plain = (string)$this->protocol->encodeRequest($method, []);
        $data = $this->protocol->decode($plain);
        $this->assertTrue(is_array($data));
        $this->assertTrue(array_key_exists('type', $data));
        $this->assertEquals('request', $data['type']);
        $plain = (string)$this->protocol->encodeResponse([]);
        $data = $this->protocol->decode($plain);
        $this->assertTrue(is_array($data));
        $this->assertTrue(array_key_exists('type', $data));
        $this->assertEquals('response', $data['type']);
    }

    public function providerTestRealData()
    {
        $assetDir = realpath(__DIR__.'/../../assets/homematic/binrpc/');
        $files = glob($assetDir.'/*.bin');
        array_walk(
            $files,
            function (&$value) {
                $value = [$value];
            }
        );

        return $files;
    }

    /**
     * @dataProvider providerTestRealData
     */
    public function testRealData(string $file)
    {
        echo $file."\n";
        $this->assertTrue(file_exists($file));
        $this->assertTrue(is_readable($file));
        $data = $this->protocol->decode(file_get_contents($file));
        print_r($data);
        $this->assertTrue(is_array($data));
    }

    public function providerTestInteger()
    {
        return [
            //            [-2147483648],
            //            [-2147483647],
            //            [mt_rand(-2147483646, -2)],
            //            [-2],
            //            [-1],
            [0],
            [1],
            [2],
            [mt_rand(2, 2147483645)],
            [2147483646],
            [2147483647],
        ];
    }

    /**
     * @dataProvider providerTestInteger
     */
    public function testInteger(int $int)
    {
        $plain = (string)$this->protocol->encodeResponse(['int' => $int]);
        $data = $this->protocol->decodeResponse($plain);
        $this->assertTrue(is_array($data));
        $this->assertTrue(is_array($data['params']));
        $this->assertTrue(array_key_exists('int', $data['params']));
        $this->assertEquals($int, $data['params']['int']);
        $this->assertTrue($data['params']['int'] === $int);
    }

    public function testInvalidInteger()
    {
        if (!defined('PHP_INT_SIZE')) {
            $this->markTestSkipped();
        }
        if (PHP_INT_SIZE == 4) {
            $this->markTestSkipped();
        }
        $this->expectException(\InvalidArgumentException::class);
        $this->protocol->encodeResponse(['int' => 9223372036854775807]);
    }

    public function providerTestBoolean()
    {
        return [
            ['A'],
            [0],
            [1],
            [true],
            [false],
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
        $this->assertTrue($data['params']['bool'] === (bool)$bool);
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
        $plain = (string)$this->protocol->encodeResponse(['foo' => ['bar' => 1337]]);
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

    public function testObject()
    {
        $data = [];
        $data['foo'] = new \stdClass();
        $data['foo']->bar = 1;
        $plain = (string)$this->protocol->encodeResponse($data);
        $data = $this->protocol->decodeResponse($plain);
        $this->assertTrue(is_array($data));
        $this->assertTrue(array_key_exists('params', $data));
        $this->assertTrue(is_array($data['params']));
        $this->assertTrue(array_key_exists('foo', $data['params']));
        $this->assertTrue(is_array($data['params']['foo']));
        $this->assertTrue(array_key_exists('bar', $data['params']['foo']));
        $this->assertTrue(is_int($data['params']['foo']['bar']));
        $this->assertEquals(1, $data['params']['foo']['bar']);
    }

    public function testArray()
    {
        $plain = (string)$this->protocol->encodeResponse([1, 2, 3]);
        $data = $this->protocol->decodeResponse($plain);
        $this->assertTrue(is_array($data));
        $this->assertTrue(array_key_exists('params', $data));
        $this->assertTrue(is_array($data['params']));
        $this->assertTrue(array_key_exists(0, $data['params']));
        $this->assertEquals(1, $data['params'][0]);
        $this->assertTrue(array_key_exists(1, $data['params']));
        $this->assertEquals(2, $data['params'][1]);
        $this->assertTrue(array_key_exists(2, $data['params']));
        $this->assertEquals(3, $data['params'][2]);
        $this->assertFalse(array_key_exists(3, $data['params']));
        $this->assertFalse(array_key_exists('foo', $data['params']));
    }

    public function providerTestFloat()
    {
        $min = pow(2, 31) * -1;
        $max = pow(2, 31) - 1;

        return [
            //            [-3.402823 * pow(10, 37)],
            //            [$min],
            //            [$min + 1],
            //            [mt_rand($min, -1)],
            //            [-532410.000000],
            //            [-1234.5678],
            //            [-2],
            //            [-1],
            //            [-0.9],
            //            [-0.8],
            //            [-0.7],
            //            [-0.6],
            //            [-0.5],
            //            [-0.4],
            //            [-0.3],
            //            [-0.2],
            //            [-0.1],
            [0],
            [0.1],
            [0.2],
            [0.3],
            [0.4],
            [0.5],
            [0.6],
            [0.7],
            [0.8],
            [0.9],
            [1],
            [2],
            [1234.5678],
            [532410.000000],
            [mt_rand(2, $max - 2)],
            [$max - 1],
            [$max],
            [3.402823 * pow(10, 37)],
        ];
    }

    /**
     * @dataProvider providerTestFloat
     */
    public function testFloat(float $float)
    {
        $plain = (string)$this->protocol->encodeResponse(['float' => (float)$float]);
        $data = $this->protocol->decodeResponse($plain);
        $this->assertTrue(is_array($data));
        $this->assertTrue(array_key_exists('params', $data));
        $this->assertTrue(is_array($data['params']));
        $this->assertTrue(array_key_exists('float', $data['params']));
        $min = $float * 0.999999;
        $max = $float * 1.000001;
        if ($float < 0) {
            $tmp = $min;
            $min = $max;
            $max = $tmp;
        }
        $this->assertTrue(
            $min <= $data['params']['float'] && $data['params']['float'] <= $max,
            sprintf(
                'Value is not in range. (value: %.6f, expect: %.6f, min: %.6f, max: %.6f)',
                $data['params']['float'],
                $float,
                $min,
                $max
            )
        );
    }

}
