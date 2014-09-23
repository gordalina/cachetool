<?php

/*
 * This file is part of CacheTool.
 *
 * (c) Samuel Gordalina <samuel.gordalina@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CacheTool\Console;

use CacheTool\Adapter\FastCGI;
use CacheTool\Adapter\Cli;
use CacheTool\CacheTool;
use CacheTool\Command;
use CacheTool\Proxy;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Yaml;

class Application extends BaseApplication implements ContainerAwareInterface
{
    protected $container;
    protected $input;

    public function __construct()
    {
        parent::__construct('phpcache', CacheTool::VERSION);
    }

    /**
     * Initializes all the composer commands
     */
    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();
        $commands[] = new Command\ApcBinDumpCommand();
        $commands[] = new Command\ApcBinLoadCommand();
        $commands[] = new Command\ApcCacheClearCommand();
        $commands[] = new Command\ApcCacheInfoCommand();
        $commands[] = new Command\ApcCacheInfoFileCommand();
        $commands[] = new Command\ApcKeyDeleteCommand();
        $commands[] = new Command\ApcKeyExistsCommand();
        $commands[] = new Command\ApcKeyFetchCommand();
        $commands[] = new Command\ApcKeyStoreCommand();
        $commands[] = new Command\ApcSmaInfoCommand();

        $commands[] = new Command\OpcacheConfigurationCommand();
        $commands[] = new Command\OpcacheResetCommand();
        $commands[] = new Command\OpcacheStatusCommand();
        $commands[] = new Command\OpcacheStatusScriptsCommand();

        return $commands;
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultInputDefinition()
    {
        $definition = parent::getDefaultInputDefinition();
        $definition->addOption(new InputOption('--fcgi', '-c', InputOption::VALUE_REQUIRED, 'If specified, used as a connection string to FastCGI server.'));
        $definition->addOption(new InputOption('--cli', null, InputOption::VALUE_NONE, 'If specified, forces adapter to cli'));

        return $definition;
    }

    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        return parent::doRun($input, $output);
    }

    /**
     * {@inheritDoc}
     */
    public function getContainer()
    {
        if ($this->container) {
            return $this->container;
        }

        $config = $this->loadConfiguration();

        if ($this->input->getOption('cli')) {
            $config['adapter'] = 'cli';
        } else if ($this->input->getOption('fcgi')) {
            $config['adapter'] = 'fastcgi';
            $config['fastcgi'] = $this->input->getOption('fcgi');
        }

        switch ($config['adapter']) {
            case 'cli':
                $adapter = new Cli();
                break;

            case 'fastcgi':
                $adapter = new FastCGI($config['fastcgi']);
                break;

            default:
                throw new \RuntimeException("Adapter `{$config['adapter']}` is not one of cli or fastcgi");
        }

        $container = new Container();
        $container->set('cache_tool', CacheTool::factory($adapter));

        return $this->container = $container;
    }

    /**
     * @return Config
     */
    protected function loadConfiguration()
    {
        $previous = null;
        $path = getcwd();

        while (($path = realpath($path)) && $path !== $previous) {
            $file = "{$path}/.cachetool.yml";

            if (is_file($file)) {
                return new Config(Yaml::parse($file));
            }

            $previous = $path;
            $path .= '/../';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
