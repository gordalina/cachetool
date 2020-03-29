<?php

namespace CacheTool\Command;

class ApcuSmaInfoCommandTest extends CommandTest
{
    public function testCommand()
    {
        $this->assertHasApcu();

        $result = $this->runCommand('apcu:sma:info -v');

        $this->assertStringContainsString('apcu_sma_info(true)', $result);
        $this->assertStringContainsString('Segments', $result);
        $this->assertStringContainsString('Segment size', $result);
        $this->assertStringContainsString('Available memory', $result);
    }
}
