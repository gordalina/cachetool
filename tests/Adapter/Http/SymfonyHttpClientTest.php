<?php

namespace CacheTool\Adapter\Http;

use CacheTool\Adapter\Http\SymfonyHttpClient;
use Symfony\Component\Process\Process;

class SymfonyHttpClientTest extends \PHPUnit\Framework\TestCase
{
    private static ?Process $process = null;

    private static function startServer(int $wait = 100000): void
    {
        // Server is already running
        if (self::$process instanceof Process) {
            return;
        }

        self::$process = new Process(['php', '-S', '127.0.0.1:9999', '-t', '.']);
        self::$process->start();

        if ($wait) {
            usleep($wait); //wait for server to get going
        }
    }

    private static function stopServer(): void
    {
        // Do nothing if server is not running
        if (!self::$process instanceof Process) {
            return;
        }

        self::$process->stop();
        self::$process = null;
    }

    public static function tearDownAfterClass(): void
    {
        // Make sure to stop server after all tests
        self::stopServer();
    }

    public function testFetch()
    {
        self::startServer();
        $client = new SymfonyHttpClient('http://localhost:9999');
        $this->assertStringStartsWith('# CacheTool', $client->fetch('README.md'));
    }

    public function testFetchRetry(): void
    {
        self::stopServer();
        self::startServer(0);
        $client = new SymfonyHttpClient('http://localhost:9999', [], 10, 10);
        $this->assertStringStartsWith('# CacheTool', $client->fetch('README.md'));
    }

    public function testFetchUnderscores()
    {
        self::startServer();
        $sslipHostname = '_.127.0.0.1.sslip.io';
        if (!gethostbynamel($sslipHostname)) {
            $this->markTestSkipped(
                "{$sslipHostname} does not resolve, sslip  DNS is not configured correctly, skipping."
            );
        }
        $client = new SymfonyHttpClient("http://{$sslipHostname}:9999");
        $this->assertStringStartsWith('# CacheTool', $client->fetch('README.md'));
    }

    public function testFetchFailed()
    {
        self::startServer();
        $client = new SymfonyHttpClient('http://localhost:9999');
        $result = unserialize($client->fetch('does-not-exist'));

        $this->assertIsArray($result);
        $this->assertEquals(false, $result['result']);
        $this->assertCount(1, $result['errors']);
    }

    public function testFetchInvalidUrl()
    {
        self::startServer();
        $client = new SymfonyHttpClient('foo');
        $result = unserialize($client->fetch('bar'));

        $this->assertIsArray($result);
        $this->assertEquals(false, $result['result']);
        $this->assertCount(1, $result['errors']);
    }

    public function testFetchRetryFailed(): void
    {
        self::stopServer();
        self::startServer(0);
        $client = new SymfonyHttpClient('http://localhost:9999', [], 1, 2);
        $result = unserialize($client->fetch('README.md'));

        $this->assertIsArray($result);
        $this->assertEquals(false, $result['result']);
        $this->assertCount(1, $result['errors']);
    }
}
