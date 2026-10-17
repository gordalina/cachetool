<?php

namespace CacheTool\Command;

class ApcuCacheInfoKeysCommandTest extends CommandTest
{
    public function testCommand()
    {
        $this->assertHasApcu();

        $result = $this->runCommand('apcu:cache:info:keys -v');

        $this->assertStringContainsString('Hits', $result);
        $this->assertStringContainsString('Key', $result);
    }

    public function testEmptyInfo()
    {
        $result = $this->runCommand('apcu:cache:info:keys', function ($code) {
            return str_contains($code, 'extension_loaded') ? true : [];
        });

        $this->assertStringContainsString('Could not fetch info from APCu', $result);
    }

    public function testKeys()
    {
        $result = $this->runCommand('apcu:cache:info:keys', [
            'cache_list' => [
                ['num_hits' => 1234, 'access_time' => 1, 'deletion_time' => 0, 'mem_size' => 2048, 'info' => 'my-key'],
            ],
        ]);

        $this->assertMatchesRegularExpression('/1,234 +\| Yes +\| No +\| 2 KiB +\| my-key/', $result);
    }
}
