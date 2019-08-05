<?php

namespace CacheTool\Command;

class ApcuSmaInfoCommandTest extends CommandTest
{
    public function testCommand()
    {
        $this->assertHasApcu();

        $result = $this->runCommand('apcu:sma:info -v');

        $this->assertContains('apcu_sma_info(true)', $result);
        $this->assertContains('Segments', $result);
        $this->assertContains('Segment size', $result);
        $this->assertContains('Available memory', $result);
    }
}
