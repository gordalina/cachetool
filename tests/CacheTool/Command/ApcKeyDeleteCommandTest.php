<?php

namespace CacheTool\Command;

class ApcKeyDeleteCommandTest extends CommandTest
{
    public function testCommand()
    {
        if (PHP_VERSION_ID >= 70000) {
            $this->markTestSkipped('Skip APC test w/ php7');
        }
        $this->assertHasApc();

        $result = $this->runCommand('apc:key:delete key -v');
        $this->assertContains('apc_delete("key")', $result);
    }
}
