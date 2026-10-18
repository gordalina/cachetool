<?php

namespace CacheTool\Command;

class OpcacheStatusScriptsCommandTest extends CommandTest
{
    public function testCommand()
    {
        $this->assertHasOpcache();

        $result = $this->runCommand('opcache:status:scripts -v');

        $this->assertStringContainsString('opcache_get_status(true)', $result);
        $this->assertStringContainsString('Hits', $result);
        $this->assertStringContainsString('Memory', $result);
        $this->assertStringContainsString('Filename', $result);
    }

    public function testExcludingScriptsWorksAsExpected()
    {
        $this->assertHasOpcache();

        $scriptsMock = [
            '/vendor/somefile.php' => [
                'full_path' => '/vendor/somefile.php',
                'hits' => 1,
                'memory_consumption' => 1024,
            ],
            '/src/my/path/to/somefile.php' => [
                'full_path' => '/src/my/path/to/somefile.php',
                'hits' => 2,
                'memory_consumption' => 1024,
            ],
            '/src/my/other/path/to/somefile.php' => [
                'full_path' => '/src/my/other/path/to/somefile.php',
                'hits' => 3,
                'memory_consumption' => 1024,
            ],
            '/vendor/someotherfile.php' => [
                'full_path' => '/vendor/someotherfile.php',
                'hits' => 4,
                'memory_consumption' => 1024,
            ],
        ];

        $result = $this->runCommand('opcache:status:scripts -v -e vendor', ['scripts' => $scriptsMock]);

        $this->assertStringContainsString('opcache_get_status(true)', $result);
        $this->assertStringContainsString('/src/my/path/to/somefile.php', $result);
        $this->assertStringNotContainsString('vendor', $result); // No findings of "vendor" expected!
    }

    public function testNoScriptsAreExcludedByDefault()
    {
        $this->assertHasOpcache();

        $scriptsMock = [
            '/vendor/somefile.php' => [
                'full_path' => '/vendor/somefile.php',
                'hits' => 1,
                'memory_consumption' => 1024,
            ],
            '/src/my/path/to/somefile.php' => [
                'full_path' => '/src/my/path/to/somefile.php',
                'hits' => 2,
                'memory_consumption' => 1024,
            ],
            '/src/my/other/path/to/somefile.php' => [
                'full_path' => '/src/my/other/path/to/somefile.php',
                'hits' => 3,
                'memory_consumption' => 1024,
            ],
            '/vendor/someotherfile.php' => [
                'full_path' => '/vendor/someotherfile.php',
                'hits' => 4,
                'memory_consumption' => 1024,
            ],
        ];

        $result = $this->runCommand('opcache:status:scripts -v', ['scripts' => $scriptsMock]);

        $this->assertStringContainsString('opcache_get_status(true)', $result);
        $this->assertStringContainsString('/vendor/somefile.php', $result);
        $this->assertStringContainsString('/src/my/path/to/somefile.php', $result);
        $this->assertStringContainsString('/src/my/other/path/to/somefile.php', $result);
        $this->assertStringContainsString('/vendor/someotherfile.php', $result);
    }

    public function testSortsDescendingAsJson()
    {
        $result = $this->runCommand('opcache:status:scripts --format=json --sort=-memory', ['scripts' => $this->scripts()]);

        $this->assertSame(['/b.php', '/c.php', '/a.php'], array_column(json_decode($result, true), 'filename'));
        $this->assertSame(['hits' => 10, 'memory' => 4096, 'filename' => '/b.php'], json_decode($result, true)[0]);
    }

    public function testSortsAscendingAsTable()
    {
        $result = $this->runCommand('opcache:status:scripts --sort=hits', ['scripts' => $this->scripts()]);

        $this->assertMatchesRegularExpression('/2 +\| 1 KiB +\| \/a.php.*\n.*3 +\| 2 KiB +\| \/c.php.*\n.*10 +\| 4 KiB +\| \/b.php/', $result);
    }

    public function testCsv()
    {
        $result = $this->runCommand('opcache:status:scripts --format=csv', ['scripts' => $this->scripts()]);

        $this->assertSame("hits,memory,filename\n2,1024,/a.php\n10,4096,/b.php\n3,2048,/c.php\n", $result);
    }

    public function testYaml()
    {
        $result = $this->runCommand('opcache:status:scripts --format=yaml', ['scripts' => $this->scripts()]);

        $this->assertStringStartsWith("- hits: 2\n  memory: 1024\n  filename: /a.php\n", $result);
    }

    public function testUnknownSortColumn()
    {
        $result = $this->runCommand('opcache:status:scripts --sort=size', ['scripts' => $this->scripts()]);

        $this->assertStringContainsString('Unknown sort column "size". Use one of: hits, memory, filename', $result);
    }

    public function testUnknownFormat()
    {
        $result = $this->runCommand('opcache:status:scripts --format=xml', ['scripts' => $this->scripts()]);

        $this->assertStringContainsString('Unknown format "xml". Use one of: table, json, csv, yaml', $result);
    }

    private function scripts()
    {
        return [
            '/a.php' => ['full_path' => '/a.php', 'hits' => 2, 'memory_consumption' => 1024],
            '/b.php' => ['full_path' => '/b.php', 'hits' => 10, 'memory_consumption' => 4096],
            '/c.php' => ['full_path' => '/c.php', 'hits' => 3, 'memory_consumption' => 2048],
        ];
    }
}
