<?php

namespace CacheTool\Command;

class OpcacheResetFileCacheCommandTest extends CommandTest
{
    public function testFileCacheNotADirectory()
    {
        $result = $this->runCommand('opcache:reset:file-cache', function ($code) {
            return str_contains($code, 'extension_loaded') ? true : '/does-not-exist';
        });

        $this->assertStringContainsString('opcache.file_cache is not set or is not a directory.', $result);
    }

    public function testAborted()
    {
        $result = $this->runCommand('opcache:reset:file-cache -n', function ($code) {
            return str_contains($code, 'extension_loaded') ? true : sys_get_temp_dir();
        });

        $this->assertStringContainsString('Aborted file deletion', $result);
    }

    public function testDeletesFiles()
    {
        $dir = sys_get_temp_dir() . '/cachetool-file-cache-' . uniqid();
        mkdir("{$dir}/sub", 0777, true);
        touch("{$dir}/sub/file.bin");

        // ini_get is mocked, the rmdir and unlink calls run for real
        $result = $this->runCommand('opcache:reset:file-cache --force', function ($code) use ($dir) {
            if (str_contains($code, 'extension_loaded')) {
                return true;
            }

            return str_contains($code, 'ini_get') ? $dir : null;
        });

        $this->assertStringContainsString('Deleted 2 files.', $result);
        $this->assertSame([], array_diff(scandir($dir), ['.', '..']));
        rmdir($dir);
    }
}
