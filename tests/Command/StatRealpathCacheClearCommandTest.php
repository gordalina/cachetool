<?php

namespace CacheTool\Command;

class StatRealpathCacheClearCommandTest extends CommandTest
{
    public function testCommand()
    {
        $result = $this->runCommand('stat:clear -v');
        $this->assertStringContainsString('stat_cache_clear()', $result);
    }
}
