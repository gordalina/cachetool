<?php

namespace CacheTool\Command;

use CacheTool\Console\Application;
use CacheTool\Console\Config;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\StreamOutput;

abstract class CommandTest extends \PHPUnit_Framework_TestCase
{
    public function runCommand($cmd)
    {
        $app = new Application(new Config(array('adapter' => 'cli')));
        $app->setAutoExit(false);

        $fp = fopen('php://memory', 'a+');

        $input = new StringInput($cmd);
        $output = new StreamOutput($fp);

        $app->run($input, $output);

        return stream_get_contents($fp, -1, 0);
    }
}
