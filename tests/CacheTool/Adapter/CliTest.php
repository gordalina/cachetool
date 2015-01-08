<?php

namespace CacheTool\Adapter;

use CacheTool\Code;

class CliTest extends \PHPUnit_Framework_TestCase
{
    public function testRun()
    {
        $cli = new Cli();
        $cli->setTempDir(sys_get_temp_dir());
        $cli->setLogger($this->getMockBuilder('Monolog\Logger')->disableOriginalConstructor()->getMock());

        $code = Code::fromString('return true;');

        $result = $cli->run($code);

        $this->assertTrue($result);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testException()
    {
        $cli = new Cli();
        $cli->setTempDir(sys_get_temp_dir());
        $cli->setLogger($this->getMockBuilder('Monolog\Logger')->disableOriginalConstructor()->getMock());

        $code = Code::fromString('throw new \Exception("test");');
        $result = $cli->run($code);
    }
}
