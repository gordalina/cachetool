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

class OpcacheCompileScriptCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('opcache:compile:script')
            ->setDescription('Compile a given script to the opcode cache')
            ->addArgument('path', InputArgument::REQUIRED)
            ->setHelp('');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->ensureExtensionLoaded('Zend OPcache');
        $path = $input->getArgument('path');
        $splFiles = array($path);

        $table = new Table($output);
        $table
            ->setHeaders(array(
                'Compiled',
                'Filename'
            ))
            ->setRows($this->processFilelist($splFiles))
        ;

        $table->render();

        return 0;
    }

    protected function processFileList($splFiles)
    {
        $list = array();

        foreach ($splFiles as $file) {
            $list[] = array(
                $this->getCacheTool()->opcache_compile_file(realpath($file)),
                realpath($file)
            );
        }

        return $list;
    }

}
