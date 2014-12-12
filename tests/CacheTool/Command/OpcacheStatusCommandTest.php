<?php

namespace CacheTool\Command;

use CacheTool\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\StreamOutput;

class OpcacheStatusCommandTest extends CommandTest
{
    public function testCommand()
    {
        $result = $this->runCommand('opcache:status -v');

        $this->assertContains('opcache_get_status(false)', $result);
        $this->assertContains('Enabled', $result);
        $this->assertContains('Number of strings', $result);
        $this->assertContains('Opcache hit rate', $result);
    }
}
