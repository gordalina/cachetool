<?php

namespace CacheTool\Command;

use CacheTool\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\StreamOutput;

class OpcacheConfigurationCommandTest extends CommandTest
{
    public function testCommand()
    {
        $result = $this->runCommand('opcache:configuration -v');

        $this->assertContains('opcache_get_configuration()', $result);
        $this->assertContains('Directive', $result);
        $this->assertContains('Value', $result);
        $this->assertContains('opcache.enable', $result);
        $this->assertContains('opcache.enable_cli', $result);
    }
}
