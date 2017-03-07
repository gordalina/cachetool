<?php

namespace CacheTool\Command;

class ApcCacheInfoCommandTest extends CommandTest
{
    public function testCommand()
    {
        if (explode('.', PHP_VERSION_ID)[0] >= 7) {
            $this->markTestSkipped('Skip APC test w/ php7');
        }
        $this->assertHasApc();

        $result = $this->runCommand('apc:cache:info');

        $this->assertContains('Slots', $result);
        $this->assertContains('Locking type', $result);
    }
}
