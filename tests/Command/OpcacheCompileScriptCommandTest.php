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

    public function testJson()
    {
        $result = $this->runCommand('opcache:compile:script src/CacheTool.php --format=json', true);

        $this->assertSame([['compiled' => true, 'filename' => realpath('src/CacheTool.php')]], json_decode($result, true));
    }
}
