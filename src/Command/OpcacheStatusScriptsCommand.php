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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class OpcacheStatusScriptsCommand extends AbstractOpcacheCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('opcache:status:scripts')
            ->setDescription('Show scripts in the opcode cache')
            ->setHelp('')
            ->addOption(
                'exclude',
                'e',
                InputOption::VALUE_OPTIONAL,
                'Exclude scripts that match this regex. Example: `.*vendor.*`. Delimiters are not needed.'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->ensureExtensionLoaded('Zend OPcache');

        $info = $this->getCacheTool()->opcache_get_status(true);
        $this->ensureSuccessfulOpcacheCall($info);

        $exclude = $input->getOption('exclude') ?? null;

        $table = new Table($output);
        $table
            ->setHeaders([
                'Hits',
                'Memory',
                'Filename'
            ])
            ->setRows($this->processFilelist($info['scripts'], $exclude))
        ;

        $table->render();

        return 0;
    }

    protected function processFileList(
        array $cacheList,
        string $exclude = null
    ) {
        $list = [];

        $filteredList = $exclude ? $this->excludeFiles($cacheList, $exclude) : $cacheList;
        foreach ($filteredList as $item) {
            $list[] = [
                number_format($item['hits']),
                Formatter::bytes($item['memory_consumption']),
                $item['full_path'],
            ];
        }

        return $list;
    }

    protected function excludeFiles(array $cacheList, string $exclude = null): array
    {
        return array_intersect_key($cacheList, array_flip(preg_grep("({$exclude})", array_keys($cacheList), \PREG_GREP_INVERT)));
    }
}
