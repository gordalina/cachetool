<?php

namespace CacheTool\Command;

class OpcacheStatusCommandTest extends CommandTest
{
    public function testCommand()
    {
        $this->assertHasOpcache();

        $result = $this->runCommand('opcache:status -v');

        $this->assertStringContainsString('opcache_get_status(false)', $result);
        $this->assertStringContainsString('Enabled', $result);
        $this->assertStringContainsString('Opcache hit rate', $result);
    }
}
