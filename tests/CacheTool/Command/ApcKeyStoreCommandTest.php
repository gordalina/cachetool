<?php

namespace CacheTool\Command;

class ApcKeyStoreCommandTest extends CommandTest
{
    public function testCommand()
    {
        $this->assertHasApc();

        $result = $this->runCommand('apc:key:store key value 3600 -v');
        $this->assertContains('apc_store("key", "value", "3600")', $result);
    }
}
