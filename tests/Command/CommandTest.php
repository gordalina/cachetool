<?php

namespace CacheTool\Command;

use CacheTool\Console\Application;
use CacheTool\Console\Config;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

abstract class CommandTest extends \PHPUnit\Framework\TestCase
{
    public function runCommand($cmd)
    {
        $app = new Application(new Config(['adapter' => 'cli']));
        $app->setAutoExit(false);

        $input = new StringInput($cmd);
        $output = new BufferedOutput();

        $app->run($input, $output);

        return $output->fetch();
    }

    protected function assertHasApcu()
    {
        if (!extension_loaded('apcu')) {
            $this->markTestSkipped('APCu extension is not loaded.');
        }
    }

    protected function assertHasOpcache()
    {
        if (!extension_loaded('Zend OPcache')) {
            return $this->markTestSkipped('OPcache extension is not loaded.');
        }

        if (!ini_get('opcache.enable_cli')) {
            return $this->markTestSkipped('OPcache extension is not enabled for the cli. (opcache.enable_cli)');
        }

        if (ini_get('opcache.file_cache_only')) {
            return $this->markTestSkipped('OPcache extension is in file_cache_only mode. (opcache.enable_cli)');
        }
    }

    protected function assertNoHHVM()
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('Not supported in HHVM');
        }
    }
}
