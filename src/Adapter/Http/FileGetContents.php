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
    public function __construct($baseUrl, protected int $maxRetries = 0, protected int $delayMs = self::DEFAULT_DELAY_MS)
    {
        parent::__construct($baseUrl);
    }

    public function fetch($filename)
    {
        $url = "{$this->baseUrl}/{$filename}";
        $retry = $this->maxRetries;

        do {
            $contents = @file_get_contents($url);
            if (false !== $contents) {
                return $contents;
            }
            usleep($this->delayMs * 1000);
        } while ($retry--);

        return serialize([
            'result' => false,
            'errors' => [
                [
                    'no' => 0,
                    'str' => "file_get_contents() call failed with url: {$url}",
                ],
            ],
        ]);
    }
}
