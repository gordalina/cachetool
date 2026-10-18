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

class ApcuCacheInfoKeysCommand extends ApcuCacheInfoCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('apcu:cache:info:keys')
            ->setDescription('Shows APCu keys cache information')
            ->addFormatOption()
            ->addSortOption()
            ->setHelp('');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->ensureExtensionLoaded('apcu');

        $info = $this->getCacheTool()->apcu_cache_info(false);
        $this->normalize($info);

        if (empty($info)) {
            throw new \RuntimeException("Could not fetch info from APCu");
        }

        $columns = [
            'hits' => ['Hits', number_format(...)],
            'access_time' => ['Accessed', fn ($time) => Formatter::bool($time > 0)],
            'deletion_time' => ['Deleted', fn ($time) => Formatter::bool($time > 0)],
            'memory_size' => ['Memory size', Formatter::bytes(...)],
            'key' => ['Key'],
        ];

        $this->renderRows($input, $output, $columns, $this->processFilelist($info['cache_list']));

        return 0;
    }

    protected function processFileList(array $cacheList)
    {
        $list = [];

        foreach ($cacheList as $item) {
            $list[] = [
                'hits' => $item['num_hits'],
                'access_time' => $item['access_time'],
                'deletion_time' => $item['deletion_time'],
                'memory_size' => $item['mem_size'],
                'key' => $item['info'],
            ];
        }

        return $list;
    }
}
