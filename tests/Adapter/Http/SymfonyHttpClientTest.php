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

        usleep(100000); //wait for server to get going
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
        $result = unserialize($client->fetch('does-not-exist'));

        $this->assertIsArray($result);
        $this->assertEquals(false, $result['result']);
        $this->assertCount(1, $result['errors']);
    }

    public function testFetchInvalidUrl()
    {
        $client = new SymfonyHttpClient('foo');
        $result = unserialize($client->fetch('bar'));

        $this->assertIsArray($result);
        $this->assertEquals(false, $result['result']);
        $this->assertCount(1, $result['errors']);
    }
}
