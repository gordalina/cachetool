<?php

/*
 * This file is part of CacheTool.
 *
 * (c) Samuel Gordalina <samuel.gordalina@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CacheTool\Command;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OpcacheConfigurationCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('opcache:configuration')
            ->setDescription('Get configuration information about the cache')
            ->setHelp('');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->ensureExtensionLoaded('Zend OPcache');

        $info = $this->getCacheTool()->opcache_get_configuration();

        $output->writeln("<info>{$info['version']['opcache_product_name']}</info> <comment>{$info['version']['version']}</comment>");

        $table = new Table($output);
        $table
            ->setHeaders(['Directive', 'Value'])
            ->setRows($this->processDirectives($info['directives']))
        ;

        $table->render();
    }

    protected function processDirectives(array $directives)
    {
        $list = [];

        foreach ($directives as $name => $value) {
            $list[] = [$name, var_export($value, true)];
        }

        return $list;
    }
}
