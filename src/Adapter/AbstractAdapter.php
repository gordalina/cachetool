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
     * @var int
     */
    protected $retries = 0;

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
     * @return int
     */
    public function getRetries()
    {
        return $this->retries;
    }

    /**
     * @param  int $retries
     * @return AbstractAdapter
     */
    public function setRetries(int $retries)
    {
        $this->retries = $retries;

        return $this;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
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
     * Calls $fn again with exponential backoff if it throws.
     * Wrap only the transport step: a retry must not hide errors of the remote code.
     *
     * @param  callable $fn
     * @return mixed
     */
    protected function retry(callable $fn)
    {
        for ($attempt = 1; ; $attempt++) {
            try {
                return $fn();
            } catch (\Exception $e) {
                if ($attempt > $this->retries) {
                    throw $e;
                }

                $delay = 100 * 2 ** ($attempt - 1);
                $this->logger->warning(sprintf(
                    'Retry %d/%d in %dms: %s',
                    $attempt,
                    $this->retries,
                    $delay,
                    $e->getMessage()
                ));
                usleep($delay * 1000);
            }
        }
    }

    /**
     * @return string
     */
    protected function createTemporaryFile()
    {
        $file = sprintf("%s/cachetool-%s.php", $this->tempDir, uniqid('', true));

        // Code::writeTo() reports a file that cannot be written
        @touch($file);
        @chmod($file, 0666);

        return $file;
    }
}
