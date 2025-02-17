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
use CacheTool\Exception\RetryableException;
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

        $process = new Process([PHP_BINARY ?: "php", $file]);
        $process->run();

        if (!$process->isSuccessful()) {
            $this->logger->error(sprintf('CLI: Could not run code: %s', $process->getErrorOutput()));
            throw new RetryableException('Process failed: ' . $process->getErrorOutput());
        }

        if (!@unlink($file)) {
            $this->logger->error(sprintf('CLI: Could not delete file: %s', $file));
        }

        return $process->getOutput();
    }
}
