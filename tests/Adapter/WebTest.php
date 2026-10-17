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

    public function testLogsWhenFileCannotBeDeleted()
    {
        $dir = sys_get_temp_dir();
        $httpMock = $this->getMockBuilder(SymfonyHttpClient::class)->disableOriginalConstructor()->getMock();
        $httpMock
            ->method('fetch')
            ->willReturnCallback(function ($filename) use ($dir) {
                unlink("{$dir}/{$filename}");
                return serialize(['errors' => [], 'result' => true]);
            });

        $handler = new \Monolog\Handler\TestHandler();
        $web = new Web($dir, $httpMock);
        $web->setLogger(new \Monolog\Logger('test', [$handler]));

        $this->assertTrue($web->run(Code::fromString('return true;')));
        $this->assertTrue($handler->hasDebugThatContains('Could not delete file'));
    }

    public function testRetriesFetchOfSameFile()
    {
        $dir = sys_get_temp_dir();
        $files = [];
        $httpMock = $this->getMockBuilder(SymfonyHttpClient::class)->disableOriginalConstructor()->getMock();
        $httpMock
            ->method('fetch')
            ->willReturnCallback(function ($filename) use ($dir, &$files) {
                $files[] = $filename;
                $this->assertFileExists("{$dir}/{$filename}");
                if (count($files) < 3) {
                    throw new \RuntimeException('HTTP Response Code is not 200, it is: 404');
                }
                return serialize(['errors' => [], 'result' => true]);
            });

        $web = new Web($dir, $httpMock);
        $web->setLogger($this->getMockBuilder(\Monolog\Logger::class)->disableOriginalConstructor()->getMock());
        $web->setRetries(2);

        $this->assertTrue($web->run(Code::fromString('return true;')));
        $this->assertCount(3, $files);
        $this->assertCount(1, array_unique($files));
        $this->assertFileDoesNotExist("{$dir}/{$files[0]}");
    }

    public function testDeletesFileWhenFetchFails()
    {
        $dir = sys_get_temp_dir();
        $files = [];
        $httpMock = $this->getMockBuilder(SymfonyHttpClient::class)->disableOriginalConstructor()->getMock();
        $httpMock
            ->method('fetch')
            ->willReturnCallback(function ($filename) use (&$files) {
                $files[] = $filename;
                throw new \RuntimeException('HTTP Response Code is not 200, it is: 404');
            });

        $web = new Web($dir, $httpMock);
        $web->setLogger($this->getMockBuilder(\Monolog\Logger::class)->disableOriginalConstructor()->getMock());

        try {
            $web->run(Code::fromString('return true;'));
            $this->fail('Expected exception');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('404', $e->getMessage());
        }
        $this->assertCount(1, $files);
        $this->assertFileDoesNotExist("{$dir}/{$files[0]}");
    }

    public function testFailChmodFile()
    {
        if (function_exists('posix_getuid') && posix_getuid() === 0) {
            $this->markTestSkipped('root can chmod /dev/null');
        }

        $httpMock = $this->getMockBuilder(SymfonyHttpClient::class)->disableOriginalConstructor()->getMock();

        // /dev/null accepts touch() but only its owner can chmod() it
        $web = new class('/dev', $httpMock) extends Web {
            protected function createFilename()
            {
                return 'null';
            }
        };
        $web->setLogger($this->getMockBuilder(\Monolog\Logger::class)->disableOriginalConstructor()->getMock());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Could not chmod file: null');
        $web->run(Code::fromString('return true;'));
    }
}
