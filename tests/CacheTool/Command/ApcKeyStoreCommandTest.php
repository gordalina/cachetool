<?php

namespace CacheTool\Command;

class ApcKeyStoreCommandTest extends CommandTest
{
    public function testCommand()
    {
        if (PHP_VERSION_ID >= 70000) {
            $this->markTestSkipped('Skip APC test w/ php7');
        }
        $this->assertHasApc();

        $result = $this->runCommand('apc:key:store key value 3600 -v');
        $this->assertContains('apc_store("key", "value", "3600")', $result);
    }
}
