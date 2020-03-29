<?php

namespace CacheTool\Command;

class OpcacheStatusScriptsCommandTest extends CommandTest
{
    public function testCommand()
    {
        $this->assertHasOpcache();

        $result = $this->runCommand('opcache:status:scripts -v');

        $this->assertStringContainsString('opcache_get_status(true)', $result);
        $this->assertStringContainsString('Hits', $result);
        $this->assertStringContainsString('Memory', $result);
        $this->assertStringContainsString('Filename', $result);
    }
}
