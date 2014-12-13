<?php

namespace CacheTool\Command;

class ApcBinLoadCommandTest extends CommandTest
{
    public function testCommand()
    {
        $this->assertHasApc();
        $this->assertNoHHVM();

        $file = tempnam(sys_get_temp_dir(), 'cachetool');

        $result = $this->runCommand("apc:bin:dump -f {$file} -v");

        $this->assertContains('apc_bin_dump(null, null)', $result);
        $this->assertGreaterThan(0, strlen(file_get_contents($file)));

        $result = $this->runCommand("apc:bin:load -f {$file} -v");

        $this->assertContains('apc_bin_load(', $result);
        $this->assertContains('Load was successful', $result);

        unlink($file);
    }
}
