<?php

/*
 * This file is part of CacheTool.
 *
 * (c) Samuel Gordalina <samuel.gordalina@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CacheTool\Console;

class Config implements \ArrayAccess
{
    private $config = array(
        'adapter' => 'fastcgi',
        'fastcgi' => '127.0.0.1:9000',
        'temp_dir' => null
    );

    public function __construct(array $config = array())
    {
        if (!empty($config)) {
            $this->config = $config;

            if (!isset($this->config['temp_dir'])) {
                $this->config['temp_dir'] = sys_get_temp_dir();
            }
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->config[$offset]);
    }

    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) {
            return $this->config[$offset];
        }
    }

    public function offsetSet($offset, $value)
    {
        $this->config[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->config[$offset]);
    }
}
