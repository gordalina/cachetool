<?php

namespace CacheTool\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ApcKeyFetchCommand extends AbstractCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('apc:key:fetch')
            ->setDescription('Shows the content of an APC key')
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

        $success = false;
        $result = $this->getCacheTool()->apc_fetch($key, $success);

        if ($success) {
            $output->writeln("<comment>APC key <info>{$key}</info> has value=<info>{$value}</info></comment>");
        } else {
            $output->writeln("<comment>APC key <info>{$key}</info> does not exist.</comment>");
            return 1;
        }

    }
}
