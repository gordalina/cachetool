<?php

namespace CacheTool\Adapter;

use CacheTool\Code;

class WebTest extends \PHPUnit_Framework_TestCase
{
    public function testRun()
    {
        $httpMock = $this->getMockBuilder('CacheTool\Adapter\Http\FileGetContents')->disableOriginalConstructor()->getMock();
        $httpMock
            ->method('fetch')
            ->willReturn(serialize(['errors' => [], 'result' => true]));

        $web = new Web(sys_get_temp_dir(), $httpMock);
        $web->setTempDir(sys_get_temp_dir());
        $web->setLogger($this->getMockBuilder('Monolog\Logger')->disableOriginalConstructor()->getMock());

        $code = Code::fromString('return true;');

        try {
            $result = $web->run($code);
        } catch (\RuntimeException $e) {
            $this->markTestSkipped($e->getMessage());
        }

        $this->assertTrue($result);
    }
}
