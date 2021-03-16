<?php

namespace CacheTool\Adapter;

use CacheTool\Adapter\Http\HttpClient;
use CacheTool\Code;

class WebTest extends \PHPUnit\Framework\TestCase
{
    public function testRun()
    {
        $httpMock = $this->getMockBuilder(HttpClient::class)->disableOriginalConstructor()->getMock();
        $httpMock
            ->method('fetch')
            ->willReturn(serialize(['errors' => [], 'result' => true]));

        $web = new Web(sys_get_temp_dir(), $httpMock);
        $web->setTempDir(sys_get_temp_dir());
        $web->setLogger($this->getMockBuilder(\Monolog\Logger::class)->disableOriginalConstructor()->getMock());

        $code = Code::fromString('return true;');

        $result = $web->run($code);
        $this->assertTrue($result);
    }
}
