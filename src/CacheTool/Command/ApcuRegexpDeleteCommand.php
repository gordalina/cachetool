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

class ApcuRegexpDeleteCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('apcu:regexp:delete')
            ->setDescription('Deletes all APCu key matching a regexp')
            ->addArgument('regexp', InputArgument::REQUIRED)
            ->setHelp('');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->ensureExtensionLoaded('apcu');

        $regexp = $input->getArgument('regexp');

        $success = $this->getCacheTool()->apcu_regexp_delete($regexp);

        if ($output->isVerbose()) {
            if ($success) {
                $output->writeln("<comment>APC keys by regexp <info>{$regexp}</info> was deleted</comment>");
            } else {
                $output->writeln("<comment>APC keys by regexp <info>{$regexp}</info> could not be deleted.</comment>");
            }
        }
        return 1;
    }
}
