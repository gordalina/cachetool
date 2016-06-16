<?php

namespace CacheTool\Console;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $config = new Config();

        $this->assertTrue(isset($config['adapter']));
        $this->assertFalse(isset($config['fastcgi']));
        $this->assertSame('fastcgi', $config['adapter']);
        $this->assertNull($config['fastcgi']);
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
