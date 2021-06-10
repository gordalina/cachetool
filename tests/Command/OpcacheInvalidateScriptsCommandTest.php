<?php

namespace CacheTool\Command;

class OpcacheInvalidateScriptsCommandTest extends CommandTest
{
    public function testCommand()
    {
        $this->assertHasOpcache();

        $result = $this->runCommand('opcache:invalidate:scripts src -v');

        $this->assertStringContainsString('opcache_get_status(', $result);
        $this->assertStringContainsString('Cleaned | Filename', $result);
    }
}
