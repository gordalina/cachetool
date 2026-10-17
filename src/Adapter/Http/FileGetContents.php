<?php

/*
 * This file is part of CacheTool.
 *
 * (c) Samuel Gordalina <samuel.gordalina@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CacheTool\Adapter\Http;

class FileGetContents extends AbstractHttp
{
    private $timeout;

    /**
     * @param string $baseUrl
     * @param float|null $timeout in seconds, null uses default_socket_timeout
     */
    public function __construct($baseUrl, $timeout = null)
    {
        $this->timeout = $timeout;
        parent::__construct($baseUrl);
    }

    public function fetch($filename)
    {
        $url = "{$this->baseUrl}/{$filename}";
        $context = $this->timeout === null ? null : stream_context_create(['http' => ['timeout' => $this->timeout]]);
        $contents = @file_get_contents($url, false, $context);

        if (false === $contents) {
            throw new \RuntimeException("file_get_contents() call failed with url: {$url}");
        }

        return $contents;
    }
}
