<?php

namespace CacheTool\Command;

class ApcuCacheInfoCommandTest extends CommandTest
{
    public function testCommand()
    {
        $this->assertHasApcu();

        $result = $this->runCommand('apcu:cache:info');

        $this->assertStringContainsString('Slots', $result);
        $this->assertStringContainsString('Locking type', $result);
    }

    public function testExtensionNotLoaded()
    {
        $result = $this->runCommand('apcu:cache:info', function ($code) {
            return str_contains($code, 'extension_loaded') ? false : null;
        });

        $this->assertStringContainsString('Extension `apcu` is not loaded', $result);
    }

    public function testEmptyInfo()
    {
        $result = $this->runCommand('apcu:cache:info', function ($code) {
            return str_contains($code, 'extension_loaded') ? true : [];
        });

        $this->assertStringContainsString('Could not fetch info from APCu', $result);
    }

    public function testNormalizesApcKeys()
    {
        $result = $this->runCommand('apcu:cache:info', [
            'nslots' => 4099,
            'ttl' => 0,
            'nhits' => 1,
            'nmisses' => 2,
            'ninserts' => 3,
            'nexpunges' => 0,
            'stime' => 1727871547,
            'mem_size' => 1024,
            'nentries' => 3,
            'memory_type' => 'mmap',
        ]);

        $this->assertStringContainsString('4099', $result);
        $this->assertStringContainsString('1 KiB', $result);
    }

    public function testJsonHasRawValues()
    {
        $result = $this->runCommand('apcu:cache:info --format=json', [
            'num_slots' => 4099,
            'ttl' => 0,
            'num_hits' => 1234,
            'num_misses' => 2,
            'num_inserts' => 3,
            'expunges' => 0,
            'start_time' => 1727871547,
            'mem_size' => 1024,
            'num_entries' => 3,
            'memory_type' => 'mmap',
        ]);

        $info = json_decode($result, true);
        $this->assertSame(1234, $info['hits']);
        $this->assertSame(1727871547, $info['start_time']);
        $this->assertSame(1024, $info['memory_size']);
        $this->assertIsBool($info['file_upload_progress']);
        $this->assertNull($info['locking_type']);
    }
}
