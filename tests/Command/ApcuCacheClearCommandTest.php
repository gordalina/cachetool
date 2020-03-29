<?php

namespace CacheTool\Command;

class ApcuCacheClearCommandTest extends CommandTest
{
    public function testCommand()
    {
        $this->assertHasApcu();

        $result = $this->runCommand('apcu:cache:clear -v');

        $this->assertStringContainsString('apcu_clear_cache()', $result);
    }
}
