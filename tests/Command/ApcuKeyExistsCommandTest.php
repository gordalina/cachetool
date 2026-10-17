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
}
