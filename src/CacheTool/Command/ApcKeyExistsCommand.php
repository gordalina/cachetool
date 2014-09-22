<?php

namespace CacheTool\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ApcKeyExistsCommand extends ApcKeyFetchCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('apc:key:exists')
            ->setDescription('Checks if an APC key exists')
            ->addArgument('key', InputArgument::REQUIRED)
            ->setHelp('');
    }
}
