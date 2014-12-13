<?php

namespace CacheTool\Command;

use CacheTool\Console\Application;
use CacheTool\Console\Config;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

abstract class CommandTest extends \PHPUnit_Framework_TestCase
{
    public function runCommand($cmd)
    {
        $app = new Application(new Config(array('adapter' => 'cli')));
        $app->setAutoExit(false);

        $input = new StringInput($cmd);
        $output = new BufferedOutput();

        $app->run($input, $output);

        return $output->fetch();
    }
}
