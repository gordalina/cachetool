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

interface HttpInterface
{
    /**
     * @param  string $filename
     * @throws \Exception if the request fails
     * @return string
     */
    public function fetch($filename);
}
