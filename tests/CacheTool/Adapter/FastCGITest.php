<?php

namespace CacheTool\Adapter;

use CacheTool\Code;

class FastCGITest extends \PHPUnit_Framework_TestCase
{
    public function testRun()
    {
        $fcgi = new FastCGI();
        $fcgi->setTempDir(sys_get_temp_dir());
        $fcgi->setLogger($this->getMockBuilder('Monolog\Logger')->disableOriginalConstructor()->getMock());

        $code = Code::fromString('return true;');

        try {
            $result = $fcgi->run($code);
        } catch (\RuntimeException $e) {
            $this->markTestSkipped($e->getMessage());
        }

        $this->assertTrue($result);
    }
}
