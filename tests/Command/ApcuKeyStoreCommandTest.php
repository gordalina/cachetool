<?php

namespace CacheTool\Command;

class ApcuKeyStoreCommandTest extends CommandTest
{
    public function testCommand()
    {
        $this->assertHasApcu();

        $result = $this->runCommand('apcu:key:store key value 3600 -v');
        $this->assertStringContainsString('apcu_store("key", "value", "3600")', $result);
    }
}
