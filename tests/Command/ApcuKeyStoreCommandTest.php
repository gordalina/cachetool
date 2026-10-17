<?php

namespace CacheTool\Command;

class ApcuKeyStoreCommandTest extends CommandTest
{
    public function testCommand()
    {
        $this->assertHasApcu();

        $result = $this->runCommand('apcu:key:store key value 3600 -v');
        $this->assertStringContainsString('apcu_store("key", "value", 3600)', $result);
    }

    public function testTtlIsRequired()
    {
        $result = $this->runCommand('apcu:key:store key value');
        $this->assertStringContainsString('Not enough arguments (missing: "ttl")', $result);
    }

    public function testNotStored()
    {
        $result = $this->runCommand('apcu:key:store key value 0 -v', function ($code) {
            return str_contains($code, 'extension_loaded');
        });

        $this->assertStringContainsString('APCu key key could not be stored.', $result);
    }

    public function testStoredKeyWithPercent()
    {
        $result = $this->runCommand('apcu:key:store Bundle%5CName value 60 -v', true);
        $this->assertStringContainsString("APCu key Bundle%5CName was stored with value='value' and ttl=60", $result);
    }
}
