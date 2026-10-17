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

class ApcuKeyStoreCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('apcu:key:store')
            ->setDescription('Store an APCu key with given value')
            ->addArgument('key', InputArgument::REQUIRED)
            ->addArgument('value', InputArgument::REQUIRED)
            ->addArgument('ttl', InputArgument::OPTIONAL, 0)
            ->setHelp('');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->ensureExtensionLoaded('apcu');

        $key = $input->getArgument('key');
        $value = $input->getArgument('value');
        $ttl = $input->getArgument('ttl');

        $success = $this->getCacheTool()->apcu_store($key, $value, $ttl);

        if ($output->isVerbose()) {
            if ($success) {
                $output->writeln(sprintf("<comment>APCu key <info>%s</info> was stored with value=<info>%s</info> and ttl=<info>%s</info></comment>", $key, var_export($value, 1), $ttl));
            } else {
                $output->writeln("<comment>APCu key <info>{$key}</info> could not be stored.</comment>");
            }
        }

        return $success ? 0 : 1;
    }
}
