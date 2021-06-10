<?php

namespace CacheTool\Command;

class StatRealpathGetCommandTest extends CommandTest
{
    public function testCommand()
    {
        $result = $this->runCommand('stat:realpath_get -v');

        $this->assertStringContainsString('stat_realpath_get()', $result);
        $this->assertStringContainsString("Path entry", $result);
    }
}
