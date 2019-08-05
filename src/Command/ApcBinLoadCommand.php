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

class ApcBinLoadCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('apc:bin:load')
            ->setDescription('Load a binary dump into the APC file and user variables')
            ->addOption('--file', '-f', InputOption::VALUE_OPTIONAL, "File to read binary data from")
            ->addOption('--no-verification', '-a', InputOption::VALUE_NONE, "Don't perform MD5 & CRC32 verification before loading data")
            ->setHelp('');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->ensureExtensionLoaded('apc');

        $file = $input->getOption('file');
        $noVerification = $input->getOption('no-verification');

        if (!$file) {
            $file = 'php://stdin';
        } else {
            if (!is_file($file) || !is_readable($file)) {
                throw new \InvalidArgumentException(sprintf("Could not read from file: %s", $file));
            }
        }

        $dump = file_get_contents($file);
        $flags = 0;

        if (!$noVerification) {
            $flags = APC_BIN_VERIFY_MD5 | APC_BIN_VERIFY_CRC32;
        }

        $success = $this->getCacheTool()->apc_bin_load($dump, $flags);

        if ($output->isVerbose()) {
            if ($success) {
                $output->writeln("<comment>Load was successful</comment>");
            } else {
                $output->writeln("<comment>Load was unsuccessful</comment>");
            }
        }

        return $success ? 0 : 1;
    }
}
