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
use Psr\Log\LoggerInterface;

abstract class AbstractAdapter
{
    /**
     * @var string
     */
    protected $tempDir;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param  Code   $code
     * @return string
     */
    abstract protected function doRun(Code $code);

    /**
     * @param  Code   $code
     * @throws \RuntimeException
     * @return mixed
     */
    public function run(Code $code)
    {
        $this->logger->debug(sprintf('Executing code: %s', $code->getCode()));
        $data = $this->doRun($code);

        $result = @unserialize($data);

        if (!is_array($result)) {
            $this->logger->debug(sprintf('Return serialized: %s', $data));
            throw new \RuntimeException('Could not unserialize data from adapter.');
        }

        $this->logger->debug(sprintf('Return errors: %s', json_encode($result['errors'])));
        $this->logger->debug(sprintf('Return result: %s', json_encode($result['result'])));

        if (empty($result['errors'])) {
            return $result['result'];
        }

        $errors = array_reduce($result['errors'], function ($carry, $error) {
            return $carry .= "{$error['str']} (error code: {$error['no']})\n";
        });

        throw new \RuntimeException($errors);
    }

    /**
     * @return string
     */
    public function getTempDir()
    {
        return $this->tempDir;
    }

    /**
     * @param string $tempDir
     */
    public function setTempDir($tempDir)
    {
        $this->tempDir = $tempDir;

        return $this;
    }

    /**
     * @param  LoggerInterface $logger
     * @return AbstractAdapter
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @return string
     */
    protected function createTemporaryFile()
    {
        $file = sprintf("%s/cachetool-%s.php", $this->tempDir, uniqid());

        touch($file);
        chmod($file, 0666);

        return $file;
    }
}
