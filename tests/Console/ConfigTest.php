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

    public function testFactoryFindsFileInCurrentDir()
    {
        $cwd = getcwd();
        $dir = sys_get_temp_dir() . '/cachetool-config-' . uniqid();
        mkdir($dir);
        file_put_contents("{$dir}/.cachetool.yml", 'adapter: cli');

        try {
            chdir($dir);
            $this->assertSame('cli', Config::factory()['adapter']);
        } finally {
            chdir($cwd);
            unlink("{$dir}/.cachetool.yml");
            rmdir($dir);
        }
    }

    public function testWindowsHomeDir()
    {
        $home = getenv('HOME');
        $server = $_SERVER;
        putenv('HOME');
        $_SERVER['HOMEDRIVE'] = 'C:';
        $_SERVER['HOMEPATH'] = '\\Users\\sam\\';

        try {
            $method = new \ReflectionMethod(Config::class, 'getUserHomeDir');
            $this->assertSame('C:\\Users\\sam', $method->invoke(null));
        } finally {
            putenv($home === false ? 'HOME' : "HOME={$home}");
            $_SERVER = $server;
        }
    }
}
