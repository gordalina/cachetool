<?php

namespace CacheTool\Command;

class ApcuKeyDeleteCommandTest extends CommandTest
{
    public function testCommand()
    {
        $this->assertHasApcu();

        $result = $this->runCommand('apcu:key:delete key -v');
        $this->assertStringContainsString('apcu_delete("key")', $result);
    }

    public function testDeleted()
    {
        $result = $this->runCommand('apcu:key:delete key -v', true);
        $this->assertStringContainsString('APCu key key was deleted', $result);
    }

    public function testJson()
    {
        $result = $this->runCommand('apcu:key:delete key --format=json', true);
        $this->assertSame(['key' => 'key', 'deleted' => true], json_decode($result, true));
    }
}
