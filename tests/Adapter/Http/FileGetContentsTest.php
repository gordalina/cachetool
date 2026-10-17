<?php

namespace CacheTool\Adapter\Http;

use CacheTool\Adapter\Http\FileGetContents;
use Symfony\Component\Process\Process;

class FileGetContentsTest extends \PHPUnit\Framework\TestCase
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
        $client = new FileGetContents('http://localhost:9999');
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
        $client = new FileGetContents('http://localhost:9999');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('file_get_contents() call failed');
        $client->fetch('does-not-exist');
    }
}
