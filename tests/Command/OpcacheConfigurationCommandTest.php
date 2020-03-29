<?php

namespace CacheTool\Command;

class OpcacheConfigurationCommandTest extends CommandTest
{
    public function testCommand()
    {
        $this->assertHasOpcache();

        $result = $this->runCommand('opcache:configuration -v');

        $this->assertStringContainsString('opcache_get_configuration()', $result);
        $this->assertStringContainsString('Directive', $result);
        $this->assertStringContainsString('Value', $result);
        $this->assertStringContainsString('opcache.enable', $result);
        $this->assertStringContainsString('opcache.enable_cli', $result);
    }
}
