<?php

namespace CacheTool\Adapter;

use CacheTool\Code;
use \Monolog\Logger;

class CliTest extends \PHPUnit\Framework\TestCase
{
    public function testRun()
    {
        $cli = new Cli();
        $cli->setTempDir(sys_get_temp_dir());
        $cli->setLogger($this->getMockBuilder(Logger::class)->disableOriginalConstructor()->getMock());

        $code = Code::fromString('return true;');

        $result = $cli->run($code);

        $this->assertTrue($result);
    }

    public function testException()
    {
        $this->expectException(\RuntimeException::class);

        $cli = new Cli();
        $cli->setTempDir(sys_get_temp_dir());
        $cli->setLogger($this->getMockBuilder(Logger::class)->disableOriginalConstructor()->getMock());

        $code = Code::fromString('throw new \Exception("test");');
        $result = $cli->run($code);
    }

    public function testPhpBinary()
    {
        $cli = new Cli();
        $cli->setTempDir(sys_get_temp_dir());
        $cli->setLogger($this->getMockBuilder(Logger::class)->disableOriginalConstructor()->getMock());

        $code = Code::fromString('return PHP_BINARY;');

        $result = $cli->run($code);

        if ('' === PHP_BINARY) {
            $this->assertNotEmpty($result);
            $this->assertFileExists($result);
        } else {
            $this->assertSame(PHP_BINARY, $result);
        }
    }
}
