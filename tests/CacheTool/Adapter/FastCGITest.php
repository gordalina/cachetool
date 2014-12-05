<?php

namespace CacheTool\Adapter;

use CacheTool\Code;

class FastCGITest extends \PHPUnit_Framework_TestCase
{
    public function testRun()
    {
        $cli = new FastCGI();
        $cli->setLogger($this->getMockBuilder('Monolog\Logger')->disableOriginalConstructor()->getMock());

        $code = Code::fromString('return true;');

        try {
            $result = $cli->run($code);
        } catch (\RuntimeException $e) {
            $this->markTestSkipped($e->getMessage());
        }

        $this->assertTrue($result);
    }
}
