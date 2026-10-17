<?php

namespace CacheTool\Command;

class OpcacheStatusCommandTest extends CommandTest
{
    public function testCommand()
    {
        $this->assertHasOpcache();

        $result = $this->runCommand('opcache:status -v');

        $this->assertStringContainsString('opcache_get_status(false)', $result);
        $this->assertStringContainsString('Enabled', $result);
        $this->assertStringContainsString('Opcache hit rate', $result);
    }

    public function testNoStatus()
    {
        $result = $this->runCommand('opcache:status', function ($code) {
            return !str_contains($code, 'opcache_get_status');
        });

        $this->assertStringContainsString('No Opcache status info available', $result);
    }

    public function testRestartPending()
    {
        $result = $this->runCommand('opcache:status', ['restart_pending' => true, 'cache_full' => true]);

        $this->assertStringContainsString('OPCache is restarting', $result);
        $this->assertStringContainsString('Also, your cache is full.', $result);
    }

    public function testFileCacheOnly()
    {
        $result = $this->runCommand('opcache:status', ['opcache_enabled' => false, 'file_cache_only' => true]);

        $this->assertMatchesRegularExpression('/file_cache_only +\| true/', $result);
    }
}
