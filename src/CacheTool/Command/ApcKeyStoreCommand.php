<?php

namespace CacheTool\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ApcKeyStoreCommand extends AbstractCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('apc:key:store')
            ->setDescription('Store an APC key with given value')
            ->addArgument('key', InputArgument::REQUIRED)
            ->addArgument('value', InputArgument::REQUIRED)
            ->addArgument('ttl', InputArgument::OPTIONAL, 0)
            ->setHelp('');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->ensureExtensionLoaded('apc');

        $key = $input->getArgument('key');
        $value = $input->getArgument('value');
        $ttl = $input->getArgument('ttl');

        $success = $this->getCacheTool()->apc_store($key, $value, $ttl);

        if ($output->isVerbose()) {
            if ($success) {
                $output->writeln("<comment>APC key <info>{$key}</info> was stored with value=<info>{$value}</info> and ttl=<info>{$ttl}</info></comment>");
            } else {
                $output->writeln("<comment>APC key <info>{$key}</info> could not be stored.</comment>");
            }
        }

        return $success ? 0 : 1;
    }
}
