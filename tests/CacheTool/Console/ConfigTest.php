<?php

namespace CacheTool\Console;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $config = new Config();

        $this->assertTrue(isset($config['adapter']));
        $this->assertTrue(isset($config['fastcgi']));
        $this->assertSame('fastcgi', $config['adapter']);
        $this->assertSame('127.0.0.1:9000', $config['fastcgi']);
    }

    public function testSet()
    {
        $config = new Config();

        $this->assertFalse(isset($config['test']));
        $config['test'] = true;
        $this->assertTrue(isset($config['test']));
        unset($config['test']);
        $this->assertFalse(isset($config['test']));
    }
}
