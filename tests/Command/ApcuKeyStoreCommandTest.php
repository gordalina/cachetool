<?php

namespace CacheTool\Command;

class ApcuKeyStoreCommandTest extends CommandTest
{
    public function testCommand()
    {
        $this->assertHasApcu();

        $result = $this->runCommand('apcu:key:store key value 3600 -v');
        $this->assertStringContainsString('apcu_store("key", "value", "3600")', $result);
    }

    public function testNotStored()
    {
        $result = $this->runCommand('apcu:key:store key value -v', function ($code) {
            return str_contains($code, 'extension_loaded');
        });

        $this->assertStringContainsString('APCu key key could not be stored.', $result);
    }
}
