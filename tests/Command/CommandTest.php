<?php

namespace CacheTool\Command;

use CacheTool\CacheTool;
use CacheTool\Code;
use CacheTool\Console\Application;
use CacheTool\Console\Config;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

abstract class CommandTest extends \PHPUnit\Framework\TestCase
{
    public function runCommand($cmd, $mockData = null)
    {
        $app = new class($mockData, new Config(['adapter' => 'cli'])) extends Application {
            protected $mockData;
            public function __construct($mockData, Config $config)
            {
                parent::__construct($config);

                $this->mockData = $mockData;
            }

            public function buildContainer(InputInterface $input)
            {
                $container = parent::buildContainer($input);

                $cacheTool = CacheTool::factory(
                    new class($this->mockData) extends \CacheTool\Adapter\Cli {
                        public function __construct(protected $mockData)
                        {}

                        public function doRun(Code $code)
                        {
                            if ($this->mockData) {
                                $wrappedMockData = [
                                    'errors' => null,
                                    'result' => $this->mockData,
                                ];
                                return serialize($wrappedMockData);
                            }

                            return parent::doRun($code);
                        }
                    },
                    $this->config['temp_dir'],
                    $this->logger
                );
                $container->set('cachetool', $cacheTool);

                return $container;
            }
        };
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
}
