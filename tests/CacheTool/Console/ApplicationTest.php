<?php

namespace CacheTool\Console;

use CacheTool\Command\CommandTest;

class ApplicationTest extends CommandTest
{
    public function testVersion()
    {
        $result = $this->runCommand('--version');
        $this->assertEquals("CacheTool version @package_version@\n", $result);
    }
}
