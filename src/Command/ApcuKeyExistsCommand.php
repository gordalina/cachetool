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

class ApcuKeyExistsCommand extends ApcuKeyFetchCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('apcu:key:exists')
            ->setDescription('Checks if an APCu key exists')
            ->addArgument('key', InputArgument::REQUIRED)
            ->setHelp('');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->ensureExtensionLoaded('apcu');

        $key = $input->getArgument('key');
        $value = $this->getCacheTool()->apcu_exists($key);

        if ($value) {
            $output->writeln("<comment>APCu key=<info>{$key}</info> exists</comment>");
        } else {
            $output->writeln("<comment>APCu key=<info>{$key}</info> does not exist.</comment>");
            return 1;
        }
    }
}
