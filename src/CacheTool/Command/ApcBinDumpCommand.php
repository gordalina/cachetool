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

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ApcBinDumpCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('apc:bin:dump')
            ->setDescription('Get a binary dump of files and user variables')
            ->addOption('--file', '-f', InputOption::VALUE_OPTIONAL)
            ->setHelp('');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->ensureExtensionLoaded('apc');

        $file = $input->getOption('file');
        $dump = $this->getCacheTool()->apc_bin_dump(null, null);

        if ($file) {
            $result = @file_put_contents($file, $dump);

            if ($result === false) {
                throw new \RuntimeException(sprintf("Could not write to file: %s", $file));
            }
        } else {
            echo $dump;
        }
    }
}
