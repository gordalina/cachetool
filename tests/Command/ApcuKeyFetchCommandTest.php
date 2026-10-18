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

    public function testJson()
    {
        $result = $this->runCommand('apcu:key:fetch key --format=json', [['a' => 1], true]);
        $this->assertSame(['key' => 'key', 'exists' => true, 'value' => ['a' => 1]], json_decode($result, true));
    }

    public function testCsvWritesObjectsAsJson()
    {
        $result = $this->runCommand('apcu:key:fetch key --format=csv', [(object) ['a' => 1], true]);
        $this->assertSame("key,exists,value\nkey,true,\"{\"\"a\"\":1}\"\n", $result);
    }

    public function testDoesNotExist()
    {
        $result = $this->runCommand('apcu:key:fetch key', fn ($code) => str_contains($code, 'extension_loaded') ?: [null, false]);
        $this->assertStringContainsString('APCu key=key does not exist.', $result);
    }
}
