<?php

namespace CacheTool\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OpcacheResetCommand extends AbstractCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('opcache:reset')
            ->setDescription('Resets the contents of the opcode cache')
            ->setHelp('');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->ensureExtensionLoaded('Zend OPcache');

        $success = $this->getCacheTool()->opcache_reset();

        return $success ? 0 : 1;
    }
}
