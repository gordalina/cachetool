<?php

namespace CacheTool\Command;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Component\HttpClient\Response\MockResponse;

class SelfUpdateCommandTest extends TestCase
{
    private string $dir;
    private string $phar;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/cachetool-self-update-' . uniqid();
        mkdir($this->dir);
        $this->phar = $this->dir . '/cachetool.phar';
        file_put_contents($this->phar, 'old');
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->dir . '/*'));
        rmdir($this->dir);
    }

    public function testUpToDate()
    {
        $tester = $this->update('12.0.0', []);

        $this->assertStringContainsString('12.0.0 is the latest version', $tester->getDisplay());
        $this->assertSame('old', file_get_contents($this->phar));
    }

    public function testUpdate()
    {
        $new = $this->dir . '/new.phar';
        $code = '$p = new Phar($argv[1]); $p->addFromString("a.php", "<?php"); $p->setStub($p->createDefaultStub("a.php"));';
        exec(escapeshellarg(PHP_BINARY) . ' -d phar.readonly=0 -r ' . escapeshellarg($code) . ' ' . escapeshellarg($new));

        $tester = $this->update('11.0.0', [new MockResponse(file_get_contents($new))]);

        $this->assertStringContainsString('Updated cachetool to 12.0.0', $tester->getDisplay());
        $this->assertFileEquals($new, $this->phar);
    }

    public function testCorruptDownload()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('not a valid phar');

        try {
            $this->update('11.0.0', [new MockResponse('garbage')]);
        } finally {
            $this->assertSame('old', file_get_contents($this->phar));
            $this->assertCount(1, glob($this->dir . '/*'));
        }
    }

    public function testNotWritable()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('is not writable');

        $tester = new CommandTester(new SelfUpdateCommand('11.0.0', $this->dir . '/missing.phar', new MockHttpClient()));
        $tester->execute([]);
    }

    public function testNoPharAsset()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Release 12.0.0 has no cachetool.phar');

        $this->update('11.0.0', [], [
            ['name' => 'cachetool.phar.gz', 'browser_download_url' => 'https://example.com/cachetool.phar.gz'],
        ]);
    }

    private function update(string $version, array $responses, ?array $assets = null): CommandTester
    {
        $release = new JsonMockResponse([
            'tag_name' => '12.0.0',
            'assets' => $assets ?? [
                ['name' => 'cachetool.phar.gz', 'browser_download_url' => 'https://example.com/cachetool.phar.gz'],
                ['name' => 'cachetool.phar', 'browser_download_url' => 'https://example.com/cachetool.phar'],
            ],
        ]);
        $client = new MockHttpClient([$release, ...$responses]);

        $tester = new CommandTester(new SelfUpdateCommand($version, $this->phar, $client));
        $tester->execute([]);

        return $tester;
    }
}
