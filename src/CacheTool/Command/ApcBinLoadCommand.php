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

use Symfony\Component\Console\Input\InputArgument;
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
            ->addArgument('file', InputArgument::REQUIRED, "File to read binary data from")
            ->addOption('--no-verification', '-a', InputOption::VALUE_NONE, "Don't perform MD5 & CRC32 verification before loading data")
            ->setHelp('');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->ensureExtensionLoaded('apc');

        $file = $input->getArgument('file');
        $noVerification = $input->getOption('no-verification');

        if (!is_file($file) || !is_readable($file)) {
            throw new \InvalidArgumentException(sprintf("Could not read from file: %s", $file));
        }

        if (!$noVerification) {
            $flags = APC_BIN_VERIFY_MD5 | APC_BIN_VERIFY_CRC32;
        }

        $success = $this->getCacheTool()->apc_bin_loadfile($file, null, $flags);

        if ($success && $output->isVerbose()) {
            $output->writeln("<comment>Load was successful</comment>");
        }

        $output->writeln("<error>Load was unsuccessful</error>");

        return $success ? 0 : 1;
    }
}
