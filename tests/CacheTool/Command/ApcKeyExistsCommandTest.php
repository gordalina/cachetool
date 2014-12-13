<?php

namespace CacheTool\Command;

class ApcKeyExistsCommandTest extends CommandTest
{
    public function testCommand()
    {
        $this->assertHasApc();

        $result = $this->runCommand('apc:key:exists key -v');
        $this->assertContains('apc_exists("key")', $result);
    }
}
