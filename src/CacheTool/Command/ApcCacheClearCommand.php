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

class ApcCacheClearCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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
