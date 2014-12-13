<?php

namespace CacheTool\Command;

class OpcacheResetCommandTest extends CommandTest
{
    public function testCommand()
    {
        $this->assertHasOpcache();

        $result = $this->runCommand('opcache:reset -v');

        $this->assertContains('opcache_reset()', $result);
    }
}
