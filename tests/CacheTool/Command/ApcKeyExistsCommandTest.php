<?php

namespace CacheTool\Command;

class ApcKeyExistsCommandTest extends CommandTest
{
    public function testCommand()
    {
        if (explode('.', PHP_VERSION_ID)[0] >= 7) {
            $this->markTestSkipped('Skip APC test w/ php7');
        }
        $this->assertHasApc();

        $result = $this->runCommand('apc:key:exists key -v');
        $this->assertContains('apc_exists("key")', $result);
    }
}
