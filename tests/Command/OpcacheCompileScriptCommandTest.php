<?php

namespace CacheTool\Command;

class OpcacheCompileScriptCommandTest extends CommandTest
{
    public function testCommand()
    {
        $this->assertHasOpcache();

        $result = $this->runCommand('opcache:compile:script src/CacheTool.php -v');

        $this->assertStringContainsString('opcache_compile_file(', $result);
        $this->assertStringContainsString('Compiled | Filename', $result);
    }
}
