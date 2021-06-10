<?php

namespace CacheTool\Command;

class StatRealpathSizeCommandTest extends CommandTest
{
    public function testCommand()
    {
        $result = $this->runCommand('stat:realpath_size -v');

        $this->assertStringContainsString('stat_realpath_size()', $result);
        $this->assertStringContainsString("Realpath cache size", $result);
    }
}
