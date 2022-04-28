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

use CacheTool\Adapter\Http\HttpInterface;
use CacheTool\Code;

class Web extends AbstractAdapter
{
    // try for 120s (the default php.ini realpath_cache_ttl setting) + 6s
    private const maxTries = 42;
    private const retryDelayS = 3;
    private $path;
    private $http;

    public function __construct($path, HttpInterface $http)
    {
        // clear CLI realpath cache
        clearstatcache(true);
        $this->path = realpath($path);
        $this->http = $http;
    }

    /**
     * {@inheritdoc}
     */
    protected function doRun(Code $code)
    {
        $filename = $this->createFilename();
        $file = $this->createWebFile($filename);
        $code->writeTo($file);

        // Some storage setups lead to created files not immediately being accessible via HTTP.
        // Try fetching up to self::maxTries times in self::retryDelay seconds intervals:
        for ($i = 0; $i < self::maxTries; $i++) {
            $content = $this->http->fetch($filename);
            $result = @unserialize($content);

            // HttpInterface::fetch() returns errors in this structure:
            if (count($result['result']['errors'] ?? []) === 0) {
                break;
            }

            // echo the erroring result to stderr
            fwrite(
                STDERR,
                printf(
                    '%s\n',
                    json_encode($result)
                )
            );
            sleep(self::retryDelayS);
        }

        if (!@unlink($file)) {
            $this->logger->debug(sprintf('Web: Could not delete file: %s', $file));
        }

        return $content;
    }

    /**
     * @param string $filename
     *
     * @return string
     */
    protected function createWebFile($filename)
    {
        $file = sprintf("%s/%s", $this->path, $filename);

        if (!@touch($file)) {
            throw new \RuntimeException(sprintf("Could not create file: %s", $filename));
        }

        if (!@chmod($file, 0664)) {
            throw new \RuntimeException(sprintf("Could not chmod file: %s", $filename));
        }

        return $file;
    }

    /**
     * @return string
     */
    protected function createFilename()
    {
        return sprintf('cachetool-%s.php', uniqid('', true));
    }
}
