<?php

namespace Tests\HRDNS\Types;

use HRDNS\Types\URL;

class URLTest extends \PHPUnit_Framework_TestCase
{

    public function testParse()
    {
        $url = new URL('http://www.google.de');
        $this->assertEquals('http', $url->getScheme());
        $this->assertEquals('', $url->getUser());
        $this->assertEquals('', $url->getPassword());
        $this->assertEquals('www.google.de', $url->getHost());
        $this->assertEquals(80, $url->getPort());
        $this->assertEquals('/', $url->getPath());
        $this->assertEquals('', $url->getQuery());
        $this->assertEquals('', $url->getFragment());
        $this->assertEquals('http://www.google.de/', $url->getURL());
    }

    public function testParseComplexUrl()
    {
        $url = new URL('https://test1:test2@test3:1337/test4/test5.html?test6=test7&test8[]=test9#test10');
        $this->assertEquals('https', $url->getScheme());
        $this->assertEquals('test1', $url->getUser());
        $this->assertEquals('test2', $url->getPassword());
        $this->assertEquals('test3', $url->getHost());
        $this->assertEquals(1337, $url->getPort());
        $this->assertEquals('/test4/test5.html', $url->getPath());
        $this->assertEquals('test6=test7&test8[]=test9', $url->getQuery());
        $this->assertEquals('test10', $url->getFragment());
        $this->assertEquals('https://test1:test2@test3:1337/test4/test5.html?test6=test7&test8[]=test9#test10',
            $url->getURL());
    }

    public function testSetCrossDomain()
    {
        $url = new URL('https://test1:test2@test3:1337/test4/test5.html?test6=test7&test8[]=test9#test10');
        $this->assertEquals('https', $url->getScheme());
        $this->assertEquals('test1', $url->getUser());
        $this->assertEquals('test2', $url->getPassword());
        $this->assertEquals('test3', $url->getHost());
        $this->assertEquals(1337, $url->getPort());
        $this->assertEquals('/test4/test5.html', $url->getPath());
        $this->assertEquals('test6=test7&test8[]=test9', $url->getQuery());
        $this->assertEquals('test10', $url->getFragment());
        $this->assertEquals('https://test1:test2@test3:1337/test4/test5.html?test6=test7&test8[]=test9#test10',
            $url->getURL());

        $url->setURL('http://www.google.de');

        $this->assertEquals('http', $url->getScheme());
        $this->assertEquals('', $url->getUser());
        $this->assertEquals('', $url->getPassword());
        $this->assertEquals('www.google.de', $url->getHost());
        $this->assertEquals(80, $url->getPort());
        $this->assertEquals('/', $url->getPath());
        $this->assertEquals('', $url->getQuery());
        $this->assertEquals('', $url->getFragment());
        $this->assertEquals('http://www.google.de/', $url->getURL());
    }

    public function testChangePath()
    {
        $url = new URL('https://test1:test2@test3:1337/test4/test5.html?test6=test7&test8[]=test9#test10');
        $this->assertEquals('https', $url->getScheme());
        $this->assertEquals('test1', $url->getUser());
        $this->assertEquals('test2', $url->getPassword());
        $this->assertEquals('test3', $url->getHost());
        $this->assertEquals(1337, $url->getPort());
        $this->assertEquals('/test4/test5.html', $url->getPath());
        $this->assertEquals('test6=test7&test8[]=test9', $url->getQuery());
        $this->assertEquals('test10', $url->getFragment());
        $this->assertEquals('https://test1:test2@test3:1337/test4/test5.html?test6=test7&test8[]=test9#test10',
            $url->getURL());

        $url->setURL('/hello.world');

        $this->assertEquals('https', $url->getScheme());
        $this->assertEquals('test1', $url->getUser());
        $this->assertEquals('test2', $url->getPassword());
        $this->assertEquals('test3', $url->getHost());
        $this->assertEquals(1337, $url->getPort());
        $this->assertEquals('/hello.world', $url->getPath());
        $this->assertEquals('', $url->getQuery());
        $this->assertEquals('', $url->getFragment());
        $this->assertEquals('https://test1:test2@test3:1337/hello.world', $url->getURL());
    }

    public function testNoSchemaAndChangePort()
    {
        $url = new URL('https://test1:test2@test3:1337/test4/test5.html?test6=test7&test8[]=test9#test10');
        $this->assertEquals('https', $url->getScheme());
        $this->assertEquals('test1', $url->getUser());
        $this->assertEquals('test2', $url->getPassword());
        $this->assertEquals('test3', $url->getHost());
        $this->assertEquals(1337, $url->getPort());
        $this->assertEquals('/test4/test5.html', $url->getPath());
        $this->assertEquals('test6=test7&test8[]=test9', $url->getQuery());
        $this->assertEquals('test10', $url->getFragment());
        $this->assertEquals('https://test1:test2@test3:1337/test4/test5.html?test6=test7&test8[]=test9#test10',
            $url->getURL());

        $url->setURL('//test3:8080');

        $this->assertEquals('https', $url->getScheme());
        $this->assertEquals('', $url->getUser());
        $this->assertEquals('', $url->getPassword());
        $this->assertEquals('test3', $url->getHost());
        $this->assertEquals(8080, $url->getPort());
        $this->assertEquals('/', $url->getPath());
        $this->assertEquals('', $url->getQuery());
        $this->assertEquals('', $url->getFragment());
        $this->assertEquals('https://test3:8080/', $url->getURL());
    }

}
