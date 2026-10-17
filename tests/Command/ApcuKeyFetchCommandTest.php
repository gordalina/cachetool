<?php

namespace CacheTool\Command;

class ApcuKeyFetchCommandTest extends CommandTest
{
    public function testCommand()
    {
        $this->assertHasApcu();

        $result = $this->runCommand('apcu:key:fetch key -v');
        $this->assertStringContainsString('apcu_fetch("key", {})', $result);
    }

    public function testFetched()
    {
        $result = $this->runCommand('apcu:key:fetch key', ['value', true]);
        $this->assertStringContainsString("APCu key=key has value='value'", $result);
    }

    public function testFetchedKeyWithPercent()
    {
        $result = $this->runCommand('apcu:key:fetch Bundle%5CName', ['value', true]);
        $this->assertStringContainsString("APCu key=Bundle%5CName has value='value'", $result);
    }
}
