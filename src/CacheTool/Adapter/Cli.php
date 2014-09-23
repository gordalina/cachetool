<?php

/*
 * This file is part of CacheTool.
 *
 * (c) Samuel Gordalina <samuel.gordalina@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CacheTool\Adapter;

use CacheTool\Code;
use Symfony\Component\Process\Process;

class Cli extends AbstractAdapter
{
    /**
     * {@inheritdoc}
     */
    protected function doRun(Code $code)
    {
        $file = $this->createTemporaryFile();
        $code->writeTo($file);

        $process = new Process("php $file");
        $process->run();

        @unlink($file);

        return $process->getOutput();
    }
}
