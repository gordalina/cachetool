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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OpcacheConfigurationCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('opcache:configuration')
            ->setDescription('Get configuration information about the cache')
            ->addFormatOption()
            ->addSortOption()
            ->setHelp('');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->ensureExtensionLoaded('Zend OPcache');

        $info = $this->getCacheTool()->opcache_get_configuration();

        if ($input->getOption('format') === 'table') {
            $output->writeln("<info>{$info['version']['opcache_product_name']}</info> <comment>{$info['version']['version']}</comment>");
        }

        $columns = [
            'directive' => ['Directive'],
            'value' => ['Value', fn ($value) => var_export($value, true)],
        ];

        $this->renderRows($input, $output, $columns, $this->processDirectives($info['directives']));

        return 0;
    }

    protected function processDirectives(array $directives)
    {
        $list = [];

        foreach ($directives as $name => $value) {
            $list[] = ['directive' => $name, 'value' => $value];
        }

        return $list;
    }
}
