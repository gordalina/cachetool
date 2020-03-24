<?php

namespace CacheTool\Adapter;

use CacheTool\Code;
use Monolog\Logger;
use CacheTool\Adapter\Http\FileGetContents;

class WebTest extends \PHPUnit\Framework\TestCase
{
    public function testRun()
    {
        $httpMock = $this->getMockBuilder(FileGetContents::class)->disableOriginalConstructor()->getMock();
        $httpMock
            ->method('fetch')
            ->willReturn(serialize(['errors' => [], 'result' => true]));

        $web = new Web(sys_get_temp_dir(), $httpMock);
        $web->setTempDir(sys_get_temp_dir());
        $web->setLogger($this->getMockBuilder(Logger::class)->disableOriginalConstructor()->getMock());

        $code = Code::fromString('return true;');

        $result = $web->run($code);
        $this->assertTrue($result);
    }
}
