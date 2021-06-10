<?php

namespace CacheTool\Console;

class ConfigTest extends \PHPUnit\Framework\TestCase
{
    public function testConstruct()
    {
        $config = new Config();

        $this->assertTrue(isset($config['adapter']));
        $this->assertSame('fastcgi', $config['adapter']);
        $this->assertSame(null, $config['fastcgi']);
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

    public function testFactoryNoFiles()
    {
        $config = Config::factory();

        $this->assertEquals($config, new Config());
    }
}
