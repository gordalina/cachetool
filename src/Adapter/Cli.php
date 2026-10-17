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
     * @var float|null
     */
    protected $timeout;

    /**
     * @param float|null $timeout in seconds, null keeps the default of 60 seconds
     */
    public function __construct($timeout = null)
    {
        $this->timeout = $timeout;
    }

    /**
     * {@inheritdoc}
     */
    protected function doRun(Code $code)
    {
        $file = $this->createTemporaryFile();
        $code->writeTo($file);

        $process = new Process([PHP_BINARY ?: "php", $file]);

        if ($this->timeout !== null) {
            $process->setTimeout($this->timeout);
        }

        try {
            $process->run();
        } finally {
            if (!@unlink($file)) {
                $this->logger->error(sprintf('CLI: Could not delete file: %s', $file));
            }
        }

        return $process->getOutput();
    }
}
