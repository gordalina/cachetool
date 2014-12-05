<?php

namespace CacheTool\Console;

use CacheTool\Command\CommandTest;

class ApplicationTest extends CommandTest
{
    public function testVersion()
    {
        $result = $this->runCommand('--version');
        $this->assertStringStartsWith('CacheTool version @package_version@', $result);
    }
}
