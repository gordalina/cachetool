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
use CacheTool\Adapter\Http\FileGetContents;
use CacheTool\Adapter\Http\SymfonyHttpClient;
use CacheTool\Adapter\Web;
use CacheTool\CacheTool;
use CacheTool\Command as CacheToolCommand;
use CacheTool\Monolog\ConsoleHandler;
use Monolog\Logger;
use SelfUpdate\SelfUpdateCommand;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Application extends BaseApplication
{
    const VERSION = '@package_version@';

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        parent::__construct('CacheTool', self::VERSION);

        $this->config = $config;
        $this->logger = new Logger('cachetool');
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultCommands(): array
    {
        $commands = parent::getDefaultCommands();
        $commands[] = new SelfUpdateCommand(
            'gordalina/cachetool',
            '@package_version@',
            'gordalina/cachetool'
        );

        if (in_array('apcu', $this->config['extensions'], true)) {
            $commands[] = new CacheToolCommand\ApcuCacheClearCommand();
            $commands[] = new CacheToolCommand\ApcuCacheInfoCommand();
            $commands[] = new CacheToolCommand\ApcuCacheInfoKeysCommand();
            $commands[] = new CacheToolCommand\ApcuKeyDeleteCommand();
            $commands[] = new CacheToolCommand\ApcuKeyExistsCommand();
            $commands[] = new CacheToolCommand\ApcuKeyFetchCommand();
            $commands[] = new CacheToolCommand\ApcuKeyStoreCommand();
            $commands[] = new CacheToolCommand\ApcuSmaInfoCommand();
            $commands[] = new CacheToolCommand\ApcuRegexpDeleteCommand();
        }

        if (in_array('opcache', $this->config['extensions'], true)) {
            $commands[] = new CacheToolCommand\OpcacheConfigurationCommand();
            $commands[] = new CacheToolCommand\OpcacheResetCommand();
            $commands[] = new CacheToolCommand\OpcacheResetFileCacheCommand();
            $commands[] = new CacheToolCommand\OpcacheStatusCommand();
            $commands[] = new CacheToolCommand\OpcacheStatusScriptsCommand();
            $commands[] = new CacheToolCommand\OpcacheInvalidateScriptsCommand();
            $commands[] = new CacheToolCommand\OpcacheCompileScriptsCommand();
            $commands[] = new CacheToolCommand\OpcacheCompileScriptCommand();
        }

        $commands[] = new CacheToolCommand\PhpEvalCommand();
        $commands[] = new CacheToolCommand\StatCacheClearCommand();
        $commands[] = new CacheToolCommand\StatRealpathGetCommand();
        $commands[] = new CacheToolCommand\StatRealpathSizeCommand();

        return $commands;
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultInputDefinition(): InputDefinition
    {
        $definition = parent::getDefaultInputDefinition();
        $definition->addOption(new InputOption('--fcgi', null, InputOption::VALUE_OPTIONAL, 'If specified, used as a connection string to FastCGI server.'));
        $definition->addOption(new InputOption('--fcgi-chroot', null, InputOption::VALUE_OPTIONAL, 'If specified, used for mapping script path to chrooted FastCGI server. --tmp-dir need to be chrooted too.'));
        $definition->addOption(new InputOption('--cli', null, InputOption::VALUE_NONE, 'If specified, forces adapter to cli'));
        $definition->addOption(new InputOption('--web', null, InputOption::VALUE_OPTIONAL, 'If specified, uses web adapter, defaults to FileGetContents. Available adapters are: FileGetContents and SymfonyHttpClient'));
        $definition->addOption(new InputOption('--web-path', null, InputOption::VALUE_OPTIONAL, 'If specified, used as a information for web adapter'));
        $definition->addOption(new InputOption('--web-url', null, InputOption::VALUE_OPTIONAL, 'If specified, used as a information for web adapter'));
        $definition->addOption(new InputOption('--web-allow-insecure', null, InputOption::VALUE_OPTIONAL, 'If specified, verify_peer and verify_host are disabled (only for SymfonyHttpClient)'));
        $definition->addOption(new InputOption('--web-basic-auth', null, InputOption::VALUE_OPTIONAL, 'If specified, used for basic authorization (only for SymfonyHttpClient)'));
        $definition->addOption(new InputOption('--web-host', null, InputOption::VALUE_OPTIONAL, 'If specified, adds a Host header to web adapter request (only for SymfonyHttpClient)'));
        $definition->addOption(new InputOption('--tmp-dir', '-t', InputOption::VALUE_REQUIRED, 'Temporary directory to write files to'));
        $definition->addOption(new InputOption('--config', '-c', InputOption::VALUE_REQUIRED, 'If specified use this yaml configuration file'));
        return $definition;
    }

    /**
     * {@inheritDoc}
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $handler = new ConsoleHandler();
        $handler->setOutput($output);
        $this->logger->pushHandler($handler);

        $exitCode = parent::doRun($input, $output);

        $handler->close();

        return $exitCode;
    }

    /**
     * {@inheritDoc}
     */
    public function doRunCommand(Command $command, InputInterface $input, OutputInterface $output)
    {
        if ($command instanceof ContainerAwareInterface) {
            $container = $this->buildContainer($input);
            $command->setContainer($container);
        }

        return parent::doRunCommand($command, $input, $output);
    }

    /**
     * @param  InputInterface     $input
     * @return ContainerInterface
     */
    public function buildContainer(InputInterface $input)
    {
        $this->parseConfiguration($input);

        $this->logger->info(sprintf('CacheTool %s', self::VERSION));
        $this->logger->debug(sprintf('Config: %s', $this->config->toJSON()));

        $cacheTool = CacheTool::factory(
            $this->getAdapter(),
            $this->config['temp_dir'],
            $this->logger
        );

        $container = new Container();
        $container->set('cachetool', $cacheTool);
        $container->set('logger', $this->logger);

        return $container;
    }

    /**
     * @param  InputInterface $input
     */
    private function parseConfiguration(InputInterface $input)
    {
        if ($input->getOption('config')) {
            $path = $input->getOption('config');

            if (!is_file($path)) {
                throw new \RuntimeException("Could not read configuration file: {$path}");
            }

            $this->config = Config::fromFile($path);
        }

        if ($input->hasParameterOption('--cli')) {
            $this->config['adapter'] = 'cli';
        } elseif ($input->hasParameterOption('--fcgi')) {
            $this->config['adapter'] = 'fastcgi';
            $this->config['fastcgiChroot'] = $input->getParameterOption('--fcgi-chroot');

            if (!is_null($input->getParameterOption('--fcgi'))) {
                $this->config['fastcgi'] = $input->getParameterOption('--fcgi');
            }
        } elseif ($input->hasParameterOption('--web')) {
            $this->config['adapter'] = 'web';
            $this->config['webClient'] = $input->getOption('web') ?? 'FileGetContents';
            $this->config['webPath'] = $input->getParameterOption('--web-path');
            $this->config['webUrl'] = $input->getParameterOption('--web-url');

            if ($this->config['webClient'] === 'SymfonyHttpClient') {
                if ($input->hasParameterOption('--web-allow-insecure')) {
                    $this->config['webAllowInsecure'] = true;
                }

                if ($input->hasParameterOption('--web-basic-auth')) {
                    $this->config['webBasicAuth'] = $input->getParameterOption('--web-basic-auth');
                }

                if ($input->hasParameterOption('--web-host')) {
                    $this->config['webHost'] = $input->getParameterOption('--web-host');
                }
            }
        }

        if ($this->config['adapter'] === 'web') {
            $this->config['http'] = $this->buildHttpClient();
        }

        if ($input->hasParameterOption('--tmp-dir') || $input->hasParameterOption('-t')) {
            $this->config['temp_dir'] = $input->getParameterOption('--tmp-dir') ?: $input->getParameterOption('-t');
        }
    }

    /**
     * @return \CacheTool\Adapter\HttpInterface
     */
    private function buildHttpClient()
    {
        if ($this->config['webClient'] == 'SymfonyHttpClient') {
            $options = [
                'headers' => [],
            ];

            if ($this->config['webAllowInsecure']) {
                $options['verify_peer'] = false;
                $options['verify_host'] = false;
            }

            if ($this->config['webBasicAuth']) {
                $options['auth_basic'] = $this->config['webBasicAuth'];
            }

            if (isset($this->config['webHost'])) {
                $options['headers']['Host'] = $this->config['webHost'];
            }

            return new SymfonyHttpClient($this->config['webUrl'], $options);
        }

        if ($this->config['webClient'] !== 'FileGetContents') {
            $this->logger->warning(sprintf(
                'Web client `%s` not supported - defaulting to FileGetContents',
                $this->config['webClient']
            ));
        }

        return new FileGetContents($this->config['webUrl']);
    }

    /**
     * @return null|\CacheTool\Adapter\AbstractAdapter
     */
    private function getAdapter()
    {
        switch ($this->config['adapter']) {
            case 'cli':
                return new Cli();
            case 'fastcgi':
                return new FastCGI($this->config['fastcgi'], $this->config['fastcgiChroot']);
            case 'web':
                return new Web($this->config['webPath'], $this->config['http']);
        }

        throw new \RuntimeException("Adapter `{$this->config['adapter']}` is not one of cli, fastcgi or web");
    }
}
