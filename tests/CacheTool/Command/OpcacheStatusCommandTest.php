<?php

namespace CacheTool\Command;

class OpcacheStatusCommandTest extends CommandTest
{
    public function testCommand()
    {
        $this->assertHasOpcache();

        $result = $this->runCommand('opcache:status -v');

        $this->assertContains('opcache_get_status(false)', $result);
        $this->assertContains('Enabled', $result);
        $this->assertContains('Opcache hit rate', $result);
    }
}
