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

    public function testJsonHasRawValues()
    {
        $result = $this->runCommand('stat:realpath_get --format=json', [
            '/app' => ['key' => 123, 'is_dir' => true, 'realpath' => '/app', 'expires' => 1727871547],
        ]);

        $this->assertSame([['path_entry' => '/app', 'key' => 123, 'is_dir' => true, 'realpath' => '/app', 'expires' => 1727871547]], json_decode($result, true));
    }
}
