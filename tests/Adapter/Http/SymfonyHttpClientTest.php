<?php

namespace CacheTool\Adapter\Http;

use CacheTool\Adapter\Http\SymfonyHttpClient;
use Symfony\Component\Process\Process;

class SymfonyHttpClientTest extends \PHPUnit\Framework\TestCase
{
    /** @var Process */
    private static $process;

    public static function setUpBeforeClass(): void
    {
        self::$process = new Process(['php', '-S', '127.0.0.1:9999', '-t', '.']);
        self::$process->start();

        // wait up to 5s for the server to accept connections
        for ($i = 0; $i < 50 && !@fsockopen('127.0.0.1', 9999); $i++) {
            usleep(100000);
        }
    }

    public static function tearDownAfterClass(): void
    {
        self::$process->stop();
    }

    public function testFetch()
    {
        $client = new SymfonyHttpClient('http://localhost:9999');
        $this->assertStringStartsWith('# CacheTool', $client->fetch('README.md'));
    }

    public function testFetchUnderscores()
    {
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
        $client = new SymfonyHttpClient('http://localhost:9999');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('is not 200, it is: 404');
        $client->fetch('does-not-exist');
    }

    public function testFetchInvalidUrl()
    {
        $client = new SymfonyHttpClient('foo');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The given url is not valid');
        $client->fetch('bar');
    }
}
