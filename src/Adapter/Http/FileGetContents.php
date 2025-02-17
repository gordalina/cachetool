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

use CacheTool\Exception\RetryableException;

class FileGetContents extends AbstractHttp
{
    public function fetch($filename)
    {
        $url = "{$this->baseUrl}/{$filename}";
        $contents = @file_get_contents($url);

        if (false === $contents) {
            throw new RetryableException(
                sprintf("file_get_contents() call failed with url: %s", $url)
            );
        }

        return $contents;
    }
}
