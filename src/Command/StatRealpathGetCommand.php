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

use CacheTool\Util\Formatter;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StatRealpathGetCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('stat:realpath_get')
            ->setDescription('Show summary information of realpath cache entries')
            ->setHelp('');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $info = $this->getCacheTool()->stat_realpath_get();

        $table = new Table($output);
        $table
            ->setHeaders([
                'Path entry',
                'key',
                'is_dir',
                'realpath',
                'expires',
            ])
            ->setRows($this->processFilelist($info))
        ;

        $table->render();
    }

    protected function processFileList(array $cacheList)
    {
        $list = [];

        foreach ($cacheList as $path_entry => $item) {
            $list[] = [
               $path_entry,
               $item['key'],
               $item['is_dir'],
               $item['realpath'],
               $item['expires'],
            ];
        }

        return $list;
    }
}
