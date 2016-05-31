<?php

namespace CacheTool\Command;

class ApcuCacheClearCommandTest extends CommandTest
{
    public function testCommand()
    {
        $this->assertHasApcu();

        $result = $this->runCommand('apcu:cache:clear -v');

        $this->assertContains('apcu_clear_cache()', $result);
    }
}
