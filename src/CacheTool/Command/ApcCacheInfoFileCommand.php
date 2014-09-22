<?php

namespace CacheTool\Command;

use CacheTool\Util\Formatter;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ApcCacheInfoFileCommand extends AbstractCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('apc:cache:info:file')
            ->setDescription('Shows APC file cache information')
            ->setHelp('');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->ensureExtensionLoaded('apc');

        $info = $this->getCacheTool()->apc_cache_info('system');

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
