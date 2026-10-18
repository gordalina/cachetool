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

    public function testJson()
    {
        $result = $this->runCommand('opcache:compile:scripts src/Util --format=json', true);

        $this->assertSame([['compiled' => true, 'filename' => realpath('src/Util/Formatter.php')]], json_decode($result, true));
    }

    public function testBatchJson()
    {
        $result = $this->runCommand('opcache:compile:scripts src/Util --batch --format=json', function ($code) {
            return str_contains($code, 'opcache_compile_file') ? 1 : true;
        });

        $this->assertSame([['compiled' => 1]], json_decode($result, true));
    }
}
