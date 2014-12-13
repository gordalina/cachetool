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

    protected function assertHasApc()
    {
        if (!extension_loaded('apc')) {
            $this->markTestSkipped('APC extension is not loaded.');
        }
    }

    protected function assertHasOpcache()
    {
        if (!extension_loaded('Zend OPcache') || !ini_get('opcache.enable_cli')) {
            $this->markTestSkipped('OPcache extension is not loaded.');
        }
    }

    protected function assertNoHHVM()
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('Not supported in HHVM');
        }
    }
}
