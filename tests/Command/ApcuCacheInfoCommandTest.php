<?php

namespace CacheTool\Command;

class ApcuCacheInfoCommandTest extends CommandTest
{
    public function testCommand()
    {
        $this->assertHasApcu();

        $result = $this->runCommand('apcu:cache:info');

        $this->assertStringContainsString('Slots', $result);
        $this->assertStringContainsString('Locking type', $result);
    }
}
