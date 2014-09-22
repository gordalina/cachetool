<?php

namespace CacheTool\Console;

class Config implements \ArrayAccess
{
    private $config = array(
        'adapter' => 'fastcgi',
        'fastcgi' => '127.0.0.1:9000',
    );

    public function __construct(array $config = array())
    {
        if (!empty($config)) {
            $this->config = $config;
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->config[$offset]);
    }

    public function offsetGet($offset, $default = null)
    {
        if ($this->offsetExists($offset)) {
            return $this->config[$offset];
        }

        return $default;
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
