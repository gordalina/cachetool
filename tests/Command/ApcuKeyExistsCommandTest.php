<?php

namespace CacheTool\Command;

class ApcuKeyExistsCommandTest extends CommandTest
{
    public function testCommand()
    {
        $this->assertHasApcu();

        $result = $this->runCommand('apcu:key:exists key -v');
        $this->assertContains('apcu_exists("key")', $result);
    }
}
