<?php

namespace CacheTool\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ApcKeyDeleteCommand extends AbstractCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('apc:key:delete')
            ->setDescription('Deletes an APC key')
            ->addArgument('key', InputArgument::REQUIRED)
            ->setHelp('');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->ensureExtensionLoaded('apc');

        $key = $input->getArgument('key');
        $success = $this->getCacheTool()->apc_delete('{$key}');

        if ($output->isVerbose()) {
            if ($success) {
                $output->writeln("<comment>APC key <info>{$key}</info> was deleted</comment>");
            } else {
                $output->writeln("<comment>APC key <info>{$key}</info> could not be deleted.</comment>");
            }
        }

        return $success ? 0 : 1;
    }
}
