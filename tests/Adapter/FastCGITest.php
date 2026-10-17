<?php

namespace CacheTool\Adapter;

use CacheTool\Code;
use CacheTool\PhpFpmRunner;
use CacheTool\Adapter\FastCGI;
use \hollodotme\FastCGI\SocketConnections\NetworkSocket;
use \hollodotme\FastCGI\Requests\PostRequest;
use \hollodotme\FastCGI\Responses\Response;
use \Monolog\Logger;

class FastCGITest extends \PHPUnit\Framework\TestCase
{
    public function testRun()
    {
        $fpm = new PhpFpmRunner();
        $fcgi = new FastCGI($fpm->socket);
        $fcgi->setTempDir(sys_get_temp_dir());
        $fcgi->setLogger($this->getMockBuilder(Logger::class)->disableOriginalConstructor()->getMock());

        $code = Code::fromString('return true;');

        $result = $fcgi->run($code);
        $this->assertTrue($result);
    }

    public function testGetScriptFileNameWithChroot()
    {
        $tmpdir = sys_get_temp_dir();
        $fcgi = new FastCGI(null, $tmpdir);
        $class = new \ReflectionClass($fcgi);
        $method = $class->getMethod('getScriptFileName');

        $this->assertSame('/test.php', $method->invoke($fcgi, "{$tmpdir}/test.php"));
    }

    public function testGetScriptFileNameWithoutChroot()
    {
        $fcgi = new FastCGI(null);
        $class = new \ReflectionClass($fcgi);
        $method = $class->getMethod('getScriptFileName');

        $this->assertSame('/tmp/test.php', $method->invoke($fcgi, '/tmp/test.php'));
    }

    public function testRunWithChroot()
    {
        $fcgi = $this->getMockBuilder(FastCGI::class)
            ->setMethods(['getScriptFileName'])
            ->setConstructorArgs(['127.0.0.1:9000', sys_get_temp_dir()])
            ->getMock();

        $reflection = new \ReflectionClass($fcgi);
        $reflectionClient = $reflection->getProperty('client');

        $clientMock = $this->getMockBuilder(\hollodotme\FastCGI\Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $reflectionClient->setValue($fcgi, $clientMock);

        $fileName = '/tmp/testRunWithChroot/test.php';
        $fcgi->expects(self::once())
            ->method('getScriptFileName')
            ->willReturn($fileName);

        $connectionMock = new NetworkSocket('127.0.0.1', '9000', 5000, 120000);
        $request = new PostRequest($fileName);
        $response = new Response("Content-type: text/html; charset=UTF-8\r\n\r\na:2:{s:6:\"result\";b:1;s:6:\"errors\";a:0:{}}", '', 0);

        $clientMock->expects(self::once())
            ->method('sendRequest')
            ->with($connectionMock, $request)
            ->willReturn($response);

        $fcgi->setTempDir(sys_get_temp_dir());
        $fcgi->setLogger($this->getMockBuilder(Logger::class)->disableOriginalConstructor()->getMock());

        $code = Code::fromString('return true;');

        $result = $fcgi->run($code);
        $this->assertTrue($result);
    }

    public function testRunWithIPv4()
    {
        $fcgi = new FastCGI('127.0.0.1:9000');

        $reflection = new \ReflectionClass($fcgi);
        $reflectionConn = $reflection->getProperty('connection');
        $connection = $reflectionConn->getValue($fcgi);

        $reflection = new \ReflectionClass($connection);
        $reflectionHost = $reflection->getProperty('host');

        $this->assertSame('127.0.0.1', $reflectionHost->getValue($connection));
    }

    public function testRunWithIPv6()
    {
        $fcgi = new FastCGI(':::9000');

        $reflection = new \ReflectionClass($fcgi);
        $reflectionConn = $reflection->getProperty('connection');
        $connection = $reflectionConn->getValue($fcgi);

        $reflection = new \ReflectionClass($connection);
        $reflectionHost = $reflection->getProperty('host');

        $this->assertSame('[::]', $reflectionHost->getValue($connection));
    }

    public function testRunThrowsWhenFpmCannotReadFile()
    {
        $fcgi = new FastCGI('127.0.0.1:9000');

        $clientMock = $this->getMockBuilder(\hollodotme\FastCGI\Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $clientMock->method('sendRequest')->willReturn(
            new Response("Status: 404 Not Found\r\nContent-type: text/html; charset=UTF-8\r\n\r\nNo input file specified.\n", '', 0)
        );
        (new \ReflectionClass($fcgi))->getProperty('client')->setValue($fcgi, $clientMock);

        $fcgi->setTempDir(sys_get_temp_dir());
        $fcgi->setLogger($this->getMockBuilder(Logger::class)->disableOriginalConstructor()->getMock());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('--tmp-dir');
        $fcgi->run(Code::fromString('return true;'));
    }

    public function testRunThrowsWhenFpmCannotFindFile()
    {
        $fpm = new PhpFpmRunner();
        // the chroot strips the temp dir from the path, so php-fpm gets a file that does not exist
        $fcgi = new FastCGI($fpm->socket, sys_get_temp_dir());
        $fcgi->setTempDir(sys_get_temp_dir());
        $handler = new \Monolog\Handler\TestHandler();
        $fcgi->setLogger(new Logger('test', [$handler]));

        try {
            $fcgi->run(Code::fromString('return true;'));
            $this->fail('Expected a RuntimeException');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('--tmp-dir', $e->getMessage());
        }

        $this->assertTrue($handler->hasDebugThatContains('File not found.'));
    }

    public function testGuessesSocketFile()
    {
        $socket = tempnam(sys_get_temp_dir(), 'php-fpm-sock');

        $fcgi = new class($socket) extends FastCGI {
            public function __construct($socket)
            {
                $this->possibleSocketFilePatterns = [$socket];
                parent::__construct();
            }
        };
        unlink($socket);

        $this->assertSame($socket, (new \ReflectionClass($fcgi))->getProperty('host')->getValue($fcgi));
    }

    public function testGetScriptFileNameOutsideChroot()
    {
        $fcgi = new FastCGI(null, '/does-not-exist');
        $method = (new \ReflectionClass($fcgi))->getMethod('getScriptFileName');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('file not in chroot directory');
        $method->invoke($fcgi, '/tmp/test.php');
    }

    public function testRunThrowsOnInvalidResponse()
    {
        $fcgi = $this->createFastCGIWithResponse(function () {
            return new Response("Content-type: text/html\r\n\r\nFatal error", '', 0);
        });
        $fcgi->setLogger($this->getMockBuilder(Logger::class)->disableOriginalConstructor()->getMock());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Error: Fatal error');
        $fcgi->run(Code::fromString('return true;'));
    }

    public function testLogsWhenFileCannotBeDeleted()
    {
        $fcgi = $this->createFastCGIWithResponse(function ($connection, $request) {
            unlink($request->getScriptFilename());
            return new Response("Content-type: text/html\r\n\r\na:2:{s:6:\"result\";b:1;s:6:\"errors\";a:0:{}}", '', 0);
        });

        $handler = new \Monolog\Handler\TestHandler();
        $fcgi->setLogger(new Logger('test', [$handler]));

        $this->assertTrue($fcgi->run(Code::fromString('return true;')));
        $this->assertTrue($handler->hasDebugThatContains('Could not delete file'));
    }

    public function testRetriesFailedRequestWithSameFile()
    {
        $files = [];
        $fcgi = $this->createFastCGIWithResponse(function ($connection, $request) use (&$files) {
            $files[] = $request->getScriptFilename();
            $this->assertFileExists($request->getScriptFilename());
            if (count($files) === 1) {
                throw new \hollodotme\FastCGI\Exceptions\ConnectException('Unable to connect');
            }
            return new Response("Content-type: text/html\r\n\r\na:2:{s:6:\"result\";b:1;s:6:\"errors\";a:0:{}}", '', 0);
        });

        $handler = new \Monolog\Handler\TestHandler();
        $fcgi->setLogger(new Logger('test', [$handler]));
        $fcgi->setRetries(1);

        $this->assertTrue($fcgi->run(Code::fromString('return true;')));
        $this->assertCount(2, $files);
        $this->assertSame($files[0], $files[1]);
        $this->assertTrue($handler->hasWarningThatContains('Retry 1/1 in 100ms: Unable to connect'));
    }

    public function testGivesUpAfterRetries()
    {
        $calls = 0;
        $fcgi = $this->createFastCGIWithResponse(function () use (&$calls) {
            $calls++;
            throw new \hollodotme\FastCGI\Exceptions\ConnectException('Unable to connect');
        });
        $fcgi->setLogger($this->getMockBuilder(Logger::class)->disableOriginalConstructor()->getMock());
        $fcgi->setRetries(2);

        try {
            $fcgi->run(Code::fromString('return true;'));
            $this->fail('Expected exception');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('FastCGI error: Unable to connect', $e->getMessage());
        }
        $this->assertSame(3, $calls);
    }

    public function testDoesNotRetryErrorsOfTheCode()
    {
        $calls = 0;
        $fcgi = $this->createFastCGIWithResponse(function () use (&$calls) {
            $calls++;
            return new Response("Content-type: text/html\r\n\r\n" . serialize(['result' => false, 'errors' => [['no' => 1, 'str' => 'boom']]]), '', 0);
        });
        $fcgi->setLogger($this->getMockBuilder(Logger::class)->disableOriginalConstructor()->getMock());
        $fcgi->setRetries(3);

        try {
            $fcgi->run(Code::fromString('return true;'));
            $this->fail('Expected exception');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('boom', $e->getMessage());
        }
        $this->assertSame(1, $calls);
    }

    private function createFastCGIWithResponse(callable $callback)
    {
        $fcgi = new FastCGI('127.0.0.1:9000');

        $clientMock = $this->getMockBuilder(\hollodotme\FastCGI\Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $clientMock->method('sendRequest')->willReturnCallback($callback);
        (new \ReflectionClass($fcgi))->getProperty('client')->setValue($fcgi, $clientMock);

        $fcgi->setTempDir(sys_get_temp_dir());

        return $fcgi;
    }
}
