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
}
