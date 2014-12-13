<?php

namespace CacheTool\Command;

class ApcCacheInfoCommandTest extends CommandTest
{
    public function testCommand()
    {
        $this->assertHasApc();

        $result = $this->runCommand('apc:cache:info');

        $this->assertContains('Slots', $result);
        $this->assertContains('Locking type', $result);
    }
}
