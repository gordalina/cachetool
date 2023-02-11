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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class OpcacheResetFileCacheCommand extends AbstractOpcacheCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('opcache:reset:file-cache')
            ->setDescription('Deletes all contents of the file cache directory')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Delete files without questioning')
            ->setHelp('');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->ensureExtensionLoaded('Zend OPcache');
        $fileCache = $this->getCacheTool()->ini_get('opcache.file_cache');

        if (!is_dir($fileCache)) {
            throw new \RuntimeException('opcache.file_cache is not set or is not a directory.');
        }

        if (!$input->getOption('force')) {
            $question = new ConfirmationQuestion(
                "Are you sure you want to delete the contents of <comment>{$fileCache}</comment>? [no] ",
                false,
                '/^y/i'
            );

            $helper = $this->getHelper('question');
            $result = $helper->ask($input, $output, $question);

            if (!$result) {
                $output->writeln('<info>Aborted file deletion</info>');
                return 0;
            }
        }


        $deleted = $this->performDelete($fileCache);
        $output->writeln("<info>Deleted <comment>{$deleted}</comment> files.</info>");

        return 0;
    }

    /**
     * @param string $directory
     */
    protected function performDelete($directory) {
        $count = 0;
        $it = new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);

        foreach($files as $file) {
            if ($file->isDir()){
                $this->getCacheTool()->_eval("rmdir('{$file->getRealPath()}');");
            } else {
                $this->getCacheTool()->_eval("unlink('{$file->getRealPath()}');");
            }
            $count += 1;
        }

        return $count;
    }
}
