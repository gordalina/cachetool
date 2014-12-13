<?php

namespace CacheTool\Command;

class OpcacheStatusScriptsCommandTest extends CommandTest
{
    public function testCommand()
    {
        $this->assertHasOpcache();

        $result = $this->runCommand('opcache:status:scripts -v');

        $this->assertContains('opcache_get_status(true)', $result);
        $this->assertContains('Hits', $result);
        $this->assertContains('Memory', $result);
        $this->assertContains('Filename', $result);
    }
}
