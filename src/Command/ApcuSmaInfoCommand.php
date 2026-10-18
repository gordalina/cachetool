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

class ApcuSmaInfoCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('apcu:sma:info')
            ->setDescription('Show APCu shared memory allocation information')
            ->addFormatOption()
            ->setHelp('');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->ensureExtensionLoaded('apcu');

        $sma = $this->getCacheTool()->apcu_sma_info(true);

        $this->renderRecord($input, $output, [
            'segments' => ['Segments', $sma['num_seg']],
            'segment_size' => ['Segment size', $sma['seg_size'], Formatter::bytes(...)],
            'available_memory' => ['Available memory', $sma['avail_mem'], Formatter::bytes(...)],
        ]);

        return 0;
    }
}
