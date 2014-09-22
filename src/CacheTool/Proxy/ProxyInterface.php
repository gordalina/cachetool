<?php

namespace CacheTool\Proxy;

use CacheTool\Adapter\AdapterInterface;

interface ProxyInterface
{
    /**
     * @return array
     */
    public function getFunctions();

    /**
     * @param  AdapterInterface $adapter
     * @return null
     */
    public function setAdapter(AdapterInterface $adapter);
}
