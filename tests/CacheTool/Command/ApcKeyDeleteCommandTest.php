<?php

namespace CacheTool\Command;

class ApcKeyDeleteCommandTest extends CommandTest
{
    public function testCommand()
    {
        $this->assertHasApc();

        $result = $this->runCommand('apc:key:delete key -v');
        $this->assertContains('apc_delete("key")', $result);
    }
}
