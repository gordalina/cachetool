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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class OpcacheCompileScriptsCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('opcache:compile:scripts')
            ->setDescription('Compile scripts from path to the opcode cache')
            ->addArgument('path', InputArgument::REQUIRED)
            ->addOption(
                'exclude',
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                'Exclude files from given path, this is relative to the path argument.'
            )
            ->addOption(
                'batch',
                null,
                InputOption::VALUE_NONE,
                'Compile all files at once, could be useful if pm.max_requests is too low'
            )
            ->setHelp('');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->ensureExtensionLoaded('Zend OPcache');
        $path = $input->getArgument('path');

        $exclude = [];
        if ($input->hasOption('exclude')) {
            $exclude = $input->getOption('exclude');
        }

        $splFiles = $this->prepareFileList($path, $exclude);
        if ($input->getOption('batch')) {
            $this->compileBatch($splFiles, $output);
        } else {
            $this->compile($splFiles, $output);
        }

        return 0;
    }

    /**
     * @param \Traversable|\SplFileInfo[] $splFiles
     * @param OutputInterface $output
     */
    protected function compile($splFiles, OutputInterface $output)
    {
        $rows = [];
        foreach ($splFiles as $file) {
            $rows[] = [
                $this->getCacheTool()->opcache_compile_file($file->getRealPath()),
                $file->getRealPath()
            ];
        }

        $table = new Table($output);
        $table
            ->setHeaders(['Compiled', 'Filename'])
            ->setRows($rows)
        ;
        $table->render();
    }

    /**
     * @param \Traversable|\SplFileInfo[] $splFiles
     * @param OutputInterface $output
     */
    protected function compileBatch($splFiles, OutputInterface $output)
    {
        $paths = [];
        foreach ($splFiles as $file) {
            $paths []= $file->getRealPath();
        }

        $compiled = $this->getCacheTool()->opcache_compile_files($paths);

        $table = new Table($output);
        $table
            ->setHeaders(['Compiled'])
            ->setRows([[$compiled]])
        ;

        $table->render();
    }

    /**
     * @param string $path
     * @param array $exclude
     *
     * @return \Traversable|\SplFileInfo[]
     */
    private function prepareFileList($path, $exclude = [])
    {
        return Finder::create()
            ->files()
            ->in($path)
            ->name('*.php')
            ->notPath('/Tests/')
            ->notPath('/tests/')
            ->notPath($exclude)
            ->ignoreUnreadableDirs()
            ->ignoreDotFiles(true)
            ->ignoreVCS(true);
    }
}
