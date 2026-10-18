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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StatRealpathGetCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('stat:realpath_get')
            ->setDescription('Show summary information of realpath cache entries')
            ->addFormatOption()
            ->addSortOption()
            ->setHelp('');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $info = $this->getCacheTool()->stat_realpath_get();

        $columns = [
            'path_entry' => ['Path entry'],
            'key' => ['key'],
            'is_dir' => ['is_dir'],
            'realpath' => ['realpath'],
            'expires' => ['expires', fn ($time) => Formatter::date($time, 'U')],
        ];

        $this->renderRows($input, $output, $columns, $this->processFilelist($info));

        return 0;
    }

    protected function processFileList(array $cacheList)
    {
        $list = [];

        foreach ($cacheList as $path_entry => $item) {
            $list[] = [
                'path_entry' => $path_entry,
                'key' => $item['key'],
                'is_dir' => $item['is_dir'],
                'realpath' => $item['realpath'],
                'expires' => $item['expires'],
            ];
        }

        return $list;
    }
}
