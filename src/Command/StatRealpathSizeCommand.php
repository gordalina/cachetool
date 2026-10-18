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

use CacheTool\Util\Formatter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StatRealpathSizeCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('stat:realpath_size')
            ->setDescription('Display size of realpath cache')
            ->addFormatOption()
            ->setHelp('');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $size = $this->getCacheTool()->stat_realpath_size();
        $this->renderRecord($input, $output, [
            'realpath_cache_size' => ['Realpath cache size', $size, Formatter::bytes(...)],
        ]);

        return 0;
    }
}
