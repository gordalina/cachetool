<?php

namespace CacheTool\Command;

use CacheTool\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\StreamOutput;

abstract class CommandTest extends \PHPUnit_Framework_TestCase
{
    public function runCommand($cmd)
    {
        $app = new Application;
        $app->setAutoExit(false);

        $fp = fopen('php://memory', 'a+');

        $input = new StringInput($cmdline);
        $output = new StreamOutput($fp);

        $app->run($input, $output);

        fseek($fp, 0);

        return stream_get_contents($fp);
    }
}
