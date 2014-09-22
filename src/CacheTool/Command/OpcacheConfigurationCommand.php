<?php

namespace CacheTool\Command;

use CacheTool\Util\Formatter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OpcacheConfigurationCommand extends AbstractCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('opcache:configuration')
            ->setDescription('Get configuration information about the cache')
            ->setHelp('');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->ensureExtensionLoaded('Zend OPcache');

        $info = $this->getCacheTool()->opcache_get_configuration();

        $output->writeln("<info>{$info['version']['opcache_product_name']}</info> <comment>{$info['version']['version']}</comment>");

        $table = $this->getHelper('table');
        $table
            ->setHeaders(array('Directive', 'Value'))
            ->setRows($this->processDirectives($info['directives']))
        ;

        $table->render($output);
    }

    protected function processDirectives(array $directives)
    {
        $list = array();

        foreach ($directives as $name => $value) {
            $list[] = array($name, var_export($value, true));
        }

        return $list;
    }
}
