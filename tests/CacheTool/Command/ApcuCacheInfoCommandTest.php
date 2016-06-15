<?php

namespace CacheTool\Command;

class ApcuCacheInfoCommandTest extends CommandTest
{
    public function testCommand()
    {
        $this->assertHasApcu();

        $result = $this->runCommand('apcu:cache:info');

        $this->assertContains('Slots', $result);
        $this->assertContains('Locking type', $result);
    }
}
