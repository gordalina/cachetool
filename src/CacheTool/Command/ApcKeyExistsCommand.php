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

class ApcKeyExistsCommand extends ApcKeyFetchCommand
{
    /**
     * {@inheritdoc}
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
