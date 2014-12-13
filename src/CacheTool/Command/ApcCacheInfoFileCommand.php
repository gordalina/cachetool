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

class ApcCacheInfoFileCommand extends ApcCacheInfoCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('apc:cache:info:file')
            ->setDescription('Shows APC file cache information')
            ->setHelp('');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->ensureExtensionLoaded('apc');

        $info = $this->getCacheTool()->apc_cache_info('system');
        $this->normalize($info);

        if (!$info) {
            throw new \RuntimeException("Could not fetch info from APC");
        }

        $header = array(
            'Hits',
            'Accessed',
            'Deleted',
            'Memory size',
            'Filename',
        );

        $table = $this->getHelper('table');
        $table
            ->setHeaders($header)
            ->setRows($this->processFilelist($info['cache_list']))
        ;

        $table->render($output);
    }

    protected function processFileList(array $cacheList)
    {
        $list = array();

        foreach ($cacheList as $item) {
            $list[] = array(
                number_format($item['num_hits']),
                $item['access_time'] > 0 ? 'Yes' : 'No',
                $item['deletion_time'] > 0 ? 'Yes' : 'No',
                Formatter::bytes($item['mem_size']),
                $this->processFilename($item['filename']),
            );
        }

        return $list;
    }

    protected function processFilename($filename)
    {
        $dir = getcwd();

        if (0 === strpos($filename, $dir)) {
            return "." . substr($filename, strlen($dir));
        }

        return $filename;
    }
}
