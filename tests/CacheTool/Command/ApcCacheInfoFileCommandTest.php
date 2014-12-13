<?php

namespace CacheTool\Command;

class ApcCacheInfoFileCommandTest extends CommandTest
{
    public function testCommand()
    {
        $this->assertHasApc();

        $result = $this->runCommand('apc:cache:info:file -v');

        $this->assertContains('apc_cache_info("system")', $result);
        $this->assertContains('Hits', $result);
        $this->assertContains('Filename', $result);
    }
}
