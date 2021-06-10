<?php

namespace CacheTool\Command;

class OpcacheCompileScriptsCommandTest extends CommandTest
{
    public function testCommand()
    {
        $this->assertHasOpcache();

        $result = $this->runCommand('opcache:compile:scripts src/Util -v');

        $this->assertStringContainsString('opcache_compile_file(', $result);
        $this->assertStringContainsString('Compiled | Filename', $result);
    }

    public function testBatchCommand()
    {
        $this->assertHasOpcache();

        $result = $this->runCommand('opcache:compile:scripts src/Util --batch -v');

        $this->assertStringContainsString('opcache_compile_files(', $result);
        $this->assertStringContainsString('Compiled', $result);
    }
}
