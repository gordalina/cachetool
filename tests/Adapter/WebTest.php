<?php

namespace CacheTool\Adapter;

use CacheTool\Adapter\Http\SymfonyHttpClient;
use CacheTool\Code;

class WebTest extends \PHPUnit\Framework\TestCase
{
    public function testRun()
    {
        $httpMock = $this->getMockBuilder(SymfonyHttpClient::class)->disableOriginalConstructor()->getMock();
        $httpMock
            ->method('fetch')
            ->willReturn(serialize(['errors' => [], 'result' => true]));

        $web = new Web(sys_get_temp_dir(), $httpMock);
        $web->setTempDir(sys_get_temp_dir());
        $web->setLogger($this->getMockBuilder(\Monolog\Logger::class)->disableOriginalConstructor()->getMock());

        $this->assertSame(sys_get_temp_dir(), $web->getTempDir());
        $code = Code::fromString('return true;');

        $result = $web->run($code);
        $this->assertTrue($result);
    }

    public function testFailCreateFile()
    {
        $this->expectException(\RuntimeException::class);

        $httpMock = $this->getMockBuilder(SymfonyHttpClient::class)->disableOriginalConstructor()->getMock();
        $httpMock
            ->method('fetch')
            ->willReturn(serialize(['errors' => [], 'result' => true]));

        $web = new Web("^", $httpMock);
        $web->setLogger($this->getMockBuilder(\Monolog\Logger::class)->disableOriginalConstructor()->getMock());

        $code = Code::fromString('return true;');

        $result = $web->run($code);
    }

    public function testFailUnserialize()
    {
        $this->expectException(\RuntimeException::class);

        $httpMock = $this->getMockBuilder(SymfonyHttpClient::class)->disableOriginalConstructor()->getMock();
        $httpMock
            ->method('fetch')
            ->willReturn("");

        $web = new Web(sys_get_temp_dir(), $httpMock);
        $web->setLogger($this->getMockBuilder(\Monolog\Logger::class)->disableOriginalConstructor()->getMock());

        $code = Code::fromString('return true;');

        $result = $web->run($code);
    }
}
