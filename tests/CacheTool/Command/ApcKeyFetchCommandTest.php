<?php

namespace CacheTool\Command;

class ApcKeyFetchCommandTest extends CommandTest
{
    public function testCommand()
    {
        $this->assertHasApc();

        $result = $this->runCommand('apc:key:fetch key -v');
        $this->assertContains('apc_fetch("key", {})', $result);
    }
}
