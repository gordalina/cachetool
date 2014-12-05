<?php

/*
 * This file is part of CacheTool.
 *
 * (c) Samuel Gordalina <samuel.gordalina@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CacheTool\Proxy;

use CacheTool\Adapter\AbstractAdapter;

interface ProxyInterface
{
    /**
     * @return string[]
     */
    public function getFunctions();

    /**
     * @param  AbstractAdapter $adapter
     * @return null
     */
    public function setAdapter(AbstractAdapter $adapter);
}
