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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ApcuCacheInfoKeysCommand extends ApcuCacheInfoCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('apcu:cache:info:keys')
            ->setDescription('Shows APCu keys cache information')
            ->setHelp('');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->ensureExtensionLoaded('apcu');

        $info = $this->getCacheTool()->apcu_cache_info(false);
        $this->normalize($info);

        if (empty($info)) {
            throw new \RuntimeException("Could not fetch info from APCu");
        }

        $header = [
            'Hits',
            'Accessed',
            'Deleted',
            'Memory size',
            'Key',
        ];

        $table = new Table($output);
        $table
            ->setHeaders($header)
            ->setRows($this->processFilelist($info['cache_list']))
        ;

        $table->render();
    }

    protected function processFileList(array $cacheList)
    {
        $list = [];

        foreach ($cacheList as $item) {
            $list[] = [
                number_format($item['num_hits']),
                $item['access_time'] > 0 ? 'Yes' : 'No',
                $item['deletion_time'] > 0 ? 'Yes' : 'No',
                Formatter::bytes($item['mem_size']),
                $item['info'],
            ];
        }

        return $list;
    }
}
