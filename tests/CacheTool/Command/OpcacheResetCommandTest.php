<?php

namespace CacheTool\Command;

use CacheTool\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\StreamOutput;

class OpcacheResetCommandTest extends CommandTest
{
    public function testCommand()
    {
        $result = $this->runCommand('opcache:reset -v');

        $this->assertContains('opcache_reset()', $result);
    }
}
