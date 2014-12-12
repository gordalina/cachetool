<?php

namespace CacheTool\Command;

use CacheTool\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\StreamOutput;

class OpcacheStatusScriptsCommandTest extends CommandTest
{
    public function testCommand()
    {
        $result = $this->runCommand('opcache:status:scripts -v');

        $this->assertContains('opcache_get_status(true)', $result);
        $this->assertContains('Hits', $result);
        $this->assertContains('Memory', $result);
        $this->assertContains('Filename', $result);
    }
}
