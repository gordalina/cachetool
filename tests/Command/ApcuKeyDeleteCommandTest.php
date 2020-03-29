<?php

namespace CacheTool\Command;

class ApcuKeyDeleteCommandTest extends CommandTest
{
    public function testCommand()
    {
        $this->assertHasApcu();

        $result = $this->runCommand('apcu:key:delete key -v');
        $this->assertStringContainsString('apcu_delete("key")', $result);
    }
}
