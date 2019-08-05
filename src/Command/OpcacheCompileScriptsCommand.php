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
            ->setHelp('');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->ensureExtensionLoaded('Zend OPcache');
        $path = $input->getArgument('path');

        $info = $this->getCacheTool()->opcache_get_status(true);

        if ($info === false) {
            throw new \RuntimeException('opcache_get_status(): No Opcache status info available.  Perhaps Opcache is disabled via opcache.enable or opcache.enable_cli?');
        }

        $splFiles = $this->prepareFileList($path);

        $table = new Table($output);
        $table
            ->setHeaders([
                'Compiled',
                'Filename'
            ])
            ->setRows($this->processFilelist($splFiles))
        ;

        $table->render();
    }

    protected function processFileList($splFiles)
    {
        $list = [];

        foreach ($splFiles as $file) {
            $list[] = [
                $this->getCacheTool()->opcache_compile_file($file->getRealPath()),
                $file->getRealPath()
            ];
        }

        return $list;
    }

    /**
     * @param string $path
     *
     * @return \Traversable|\SplFileInfo[]
     */
    private function prepareFileList($path)
    {
        return Finder::create()
            ->files()
            ->in($path)
            ->name('*.php')
            ->notPath('/Tests/')
            ->notPath('/tests/')
            ->ignoreUnreadableDirs()
            ->ignoreDotFiles(true)
            ->ignoreVCS(true);
    }
}
