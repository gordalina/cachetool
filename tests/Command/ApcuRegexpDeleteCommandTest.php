<?php

namespace CacheTool\Command;

class ApcuRegexpDeleteCommandTest extends CommandTest
{
    public function testCommand()
    {
        $this->assertHasApcu();

        $result = $this->runCommand('apcu:regexp:delete /test/ -v');

        $this->assertStringContainsString('apcu_regexp_delete("\/test\/")', $result);
    }

    public function testCommandFailure()
    {
        $this->assertHasApcu();

        $result = $this->runCommand('apcu:regexp:delete invalid -v');

        $this->assertStringContainsString('InvalidArgumentException', $result);
    }

    public function testNotDeleted()
    {
        $result = $this->runCommand('apcu:regexp:delete /test/ -v', function ($code) {
            return str_contains($code, 'extension_loaded');
        });

        $this->assertStringContainsString('APC keys by regexp /test/ could not be deleted.', $result);
    }
}
