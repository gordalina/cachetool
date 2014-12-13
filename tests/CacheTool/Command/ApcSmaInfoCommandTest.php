<?php

namespace CacheTool\Command;

class ApcSmaInfoCommandTest extends CommandTest
{
    public function testCommand()
    {
        $this->assertHasApc();

        $result = $this->runCommand('apc:sma:info -v');

        $this->assertContains('apc_sma_info(true)', $result);
        $this->assertContains('Segments', $result);
        $this->assertContains('Segment size', $result);
        $this->assertContains('Available memory', $result);
    }
}
