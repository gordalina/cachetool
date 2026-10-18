<?php

namespace CacheTool\Command;

class ApcuKeyExistsCommandTest extends CommandTest
{
    public function testCommand()
    {
        $this->assertHasApcu();

        $result = $this->runCommand('apcu:key:exists key -v');
        $this->assertStringContainsString('apcu_exists("key")', $result);
    }

    public function testExists()
    {
        $result = $this->runCommand('apcu:key:exists key', true);
        $this->assertStringContainsString('APCu key=key exists', $result);
    }

    public function testJson()
    {
        $result = $this->runCommand('apcu:key:exists key --format=json', true);
        $this->assertSame(['key' => 'key', 'exists' => true], json_decode($result, true));
    }

    public function testDoesNotExist()
    {
        $result = $this->runCommand('apcu:key:exists key', fn ($code) => str_contains($code, 'extension_loaded'));
        $this->assertStringContainsString('APCu key=key does not exist.', $result);
    }
}
