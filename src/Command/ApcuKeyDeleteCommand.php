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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ApcuKeyDeleteCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('apcu:key:delete')
            ->setDescription('Deletes an APCu key')
            ->addArgument('key', InputArgument::REQUIRED)
            ->setHelp('');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->ensureExtensionLoaded('apcu');

        $key = $input->getArgument('key');
        $success = $this->getCacheTool()->apcu_delete($key);

        if ($output->isVerbose()) {
            if ($success) {
                $output->writeln("<comment>APCu key <info>{$key}</info> was deleted</comment>");
            } else {
                $output->writeln("<comment>APCu key <info>{$key}</info> could not be deleted.</comment>");
            }
        }

        return $success ? 0 : 1;
    }
}
