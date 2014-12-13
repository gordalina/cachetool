<?php

namespace CacheTool\Command;

class OpcacheConfigurationCommandTest extends CommandTest
{
    public function testCommand()
    {
        $this->assertHasOpcache();

        $result = $this->runCommand('opcache:configuration -v');

        $this->assertContains('opcache_get_configuration()', $result);
        $this->assertContains('Directive', $result);
        $this->assertContains('Value', $result);
        $this->assertContains('opcache.enable', $result);
        $this->assertContains('opcache.enable_cli', $result);
    }
}
