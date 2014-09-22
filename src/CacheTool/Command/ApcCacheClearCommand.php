<?php

namespace CacheTool\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ApcCacheClearCommand extends AbstractCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('apc:cache:clear')
            ->setDescription('Clears APC cache (user, system or all)')
            ->addArgument('cache_type', InputArgument::REQUIRED, 'Available types: user, system or all')
            ->setHelp('');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->ensureExtensionLoaded('apc');

        $cacheType = $input->getArgument('cache_type');

        if (!in_array($cacheType, array('user', 'system', 'all'))) {
            throw new \InvalidArgumentException('type argument must be user, system or all');
        }

        if ($cacheType === 'all') {
            $this->getCacheTool()->apc_clear_cache('user');
        }

        $this->getCacheTool()->apc_clear_cache($cacheType);
    }
}
