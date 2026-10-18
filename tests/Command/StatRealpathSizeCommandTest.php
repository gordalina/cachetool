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

    public function testJson()
    {
        $result = $this->runCommand('stat:realpath_size --format=json', 1234);

        $this->assertSame(['realpath_cache_size' => 1234], json_decode($result, true));
    }
}
