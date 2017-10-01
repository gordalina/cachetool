<?php

namespace CacheTool\Command;

class ApcKeyFetchCommandTest extends CommandTest
{
    public function testCommand()
    {
        if (PHP_VERSION_ID >= 70000) {
            $this->markTestSkipped('Skip APC test w/ php7');
        }
        $this->assertHasApc();

        $result = $this->runCommand('apc:key:fetch key -v');
        $this->assertContains('apc_fetch("key", {})', $result);
    }
}
