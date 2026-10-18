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

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class OpcacheInvalidateScriptsCommand extends AbstractOpcacheCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('opcache:invalidate:scripts')
            ->setDescription('Remove scripts from the opcode cache')
            ->addArgument('path', InputArgument::REQUIRED)
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force scripts invalidation')
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
        $path = $input->getArgument('path');
        $force = $input->getOption('force');

        $info = $this->getCacheTool()->opcache_get_status(true);
        $this->ensureSuccessfulOpcacheCall($info);

        $columns = [
            'invalidated' => ['Invalidated', fn ($value) => var_export($value, true)],
            'filename' => ['Filename'],
        ];

        $this->renderRows($input, $output, $columns, $this->processFilelist($info['scripts'], $path, $force));

        return 0;
    }

    protected function processFileList(array $cacheList, $path, $force)
    {
        $processed = [];
        $toInvalidate = [];

        sort($cacheList);

        foreach ($cacheList as $item) {
            $filename = $item['full_path'];
            if (preg_match('|' . $path . '|', $filename)) {
                $toInvalidate[] = $filename;
            }
        }

        $result = $this->getCacheTool()->opcache_invalidate_many($toInvalidate, $force);

        foreach ($toInvalidate as $idx => $filename) {
            $processed[] = ['invalidated' => $result[$idx], 'filename' => $filename];
        }

        return $processed;
    }
}
