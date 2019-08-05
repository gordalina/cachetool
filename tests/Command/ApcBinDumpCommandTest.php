<?php

namespace CacheTool\Command;

class ApcBinDumpCommandTest extends CommandTest
{
    public function testCommand()
    {
        if (PHP_VERSION_ID >= 70000) {
            $this->markTestSkipped('Skip APC test w/ php7');
        }
        $this->assertHasApc();
        $this->assertNoHHVM();

        ob_start();
        $result = $this->runCommand('apc:bin:dump -v');
        ob_end_clean();

        $this->assertContains('apc_bin_dump(null, null)', $result);
    }

    public function testCommandWithFile()
    {
        if (PHP_VERSION_ID >= 70000) {
            $this->markTestSkipped('Skip APC test w/ php7');
        }
        $this->assertHasApc();
        $this->assertNoHHVM();

        $file = tempnam(sys_get_temp_dir(), 'cachetool');

        $result = $this->runCommand("apc:bin:dump -f {$file} -v");

        $this->assertContains('apc_bin_dump(null, null)', $result);
        $this->assertGreaterThan(0, strlen(file_get_contents($file)));

        unlink($file);
    }
}
