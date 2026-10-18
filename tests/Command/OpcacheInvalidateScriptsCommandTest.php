<?php

namespace CacheTool\Command;

class OpcacheInvalidateScriptsCommandTest extends CommandTest
{
    public function testCommand()
    {
        $this->assertHasOpcache();

        $result = $this->runCommand('opcache:invalidate:scripts src -v');

        $this->assertStringContainsString('opcache_get_status(', $result);
        $this->assertStringContainsString('Invalidated | Filename', $result);
    }

    public function testFileCacheOnly()
    {
        $result = $this->runCommand('opcache:invalidate:scripts src', ['opcache_enabled' => false, 'file_cache_only' => true]);

        $this->assertStringContainsString("Couldn't execute command because opcache", $result);
    }

    public function testInvalidatesMatchingScripts()
    {
        $result = $this->runCommand('opcache:invalidate:scripts src', function ($code) {
            if (str_contains($code, 'opcache_get_status')) {
                return ['opcache_enabled' => true, 'scripts' => [
                    ['full_path' => '/app/vendor/b.php'],
                    ['full_path' => '/app/src/a.php'],
                ]];
            }

            return str_contains($code, 'opcache_invalidate') ? [true] : true;
        });

        $this->assertMatchesRegularExpression('/true +\| \/app\/src\/a.php/', $result);
        $this->assertStringNotContainsString('vendor', $result);
    }

    public function testJson()
    {
        $result = $this->runCommand('opcache:invalidate:scripts src --format=json', function ($code) {
            if (str_contains($code, 'opcache_get_status')) {
                return ['opcache_enabled' => true, 'scripts' => [['full_path' => '/app/src/a.php']]];
            }

            return str_contains($code, 'opcache_invalidate') ? [true] : true;
        });

        $this->assertSame([['invalidated' => true, 'filename' => '/app/src/a.php']], json_decode($result, true));
    }
}
