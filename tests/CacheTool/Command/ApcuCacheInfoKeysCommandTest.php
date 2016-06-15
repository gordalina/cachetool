<?php

namespace CacheTool\Command;

class ApcuCacheInfoKeysCommandTest extends CommandTest
{
    public function testCommand()
    {
        $this->assertHasApcu();

        $result = $this->runCommand('apcu:cache:info:keys -v');

        $this->assertContains('Hits', $result);
        $this->assertContains('Key', $result);
    }
}
