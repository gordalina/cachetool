<?php

namespace CacheTool\Adapter\Http;

use CacheTool\Adapter\Http\FileGetContents;
use CacheTool\Code;
use Symfony\Component\Process\Process;

class FileGetContentsTest extends \PHPUnit\Framework\TestCase
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
        $client = new FileGetContents('http://localhost:9999');
        $this->assertStringStartsWith('# CacheTool', $client->fetch('README.md'));
    }

    public function testFetchUnderscores()
    {
        $client = new SymfonyHttpClient('http://_.127.0.0.1.sslip.io:9999');
        $this->assertStringStartsWith('# CacheTool', $client->fetch('README.md'));
    }

    public function testFetchFailed()
    {
        $client = new FileGetContents('http://localhost:9999');
        $result = unserialize($client->fetch('does-not-exist'));

        $this->assertIsArray($result);
        $this->assertEquals(false, $result['result']);
        $this->assertCount(1, $result['errors']);
    }
}
