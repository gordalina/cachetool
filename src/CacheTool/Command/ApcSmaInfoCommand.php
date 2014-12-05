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

class ApcSmaInfoCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('apc:sma:info')
            ->setDescription('Show APC shared memory allocation information')
            ->setHelp('');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->ensureExtensionLoaded('apc');

        $sma = $this->getCacheTool()->apc_sma_info(true);

        $output->writeln(sprintf("<comment>Segments: <info>%s</info></comment>", $sma['num_seg']));
        $output->writeln(sprintf("<comment>Segment size: <info>%s</info></comment>", Formatter::bytes($sma['seg_size'])));
        $output->writeln(sprintf("<comment>Available memory: <info>%s</info></comment>", Formatter::bytes($sma['avail_mem'])));
    }
}
