<?php

namespace CacheTool;

use CacheTool\CacheTool;
use CacheTool\Adapter;
use Monolog\Logger;

class CacheToolTest extends \PHPUnit\Framework\TestCase
{
    public function testConstruct()
    {
        $cachetool = new CacheTool();
        $this->assertInstanceOf(CacheTool::class, $cachetool);
    }

    public function testFactory()
    {
        $cachetool = CacheTool::factory(null, null, $this->getLogger());

        $this->assertCount(3, $cachetool->getProxies());
        $this->assertNull($cachetool->getAdapter());
    }

    public function testFactoryWithAdapter()
    {
        $adapter = new Adapter\FastCGI();
        $cachetool = CacheTool::factory($adapter, null, $this->getLogger());

        $this->assertCount(3, $cachetool->getProxies());
        $this->assertSame($adapter, $cachetool->getAdapter());
    }

    public function testFactoryWithAdapterAndLogger()
    {
        $adapter = new Adapter\FastCGI();
        $logger = $this->getLogger();
        $cachetool = CacheTool::factory($adapter, null, $logger);

        $this->assertCount(3, $cachetool->getProxies());
        $this->assertSame($adapter, $cachetool->getAdapter());
        $this->assertSame($logger, $cachetool->getLogger());
    }

    public function testInexistentFunction()
    {
        $this->expectException(\InvalidArgumentException::class);

        $cachetool = new CacheTool(null, $this->getLogger());
        $cachetool->doesNotExist();
    }

    public function testInexistentWithMagicCallFunction()
    {
        $this->expectException(\InvalidArgumentException::class);

        $cachetool = new CacheTool(null, $this->getLogger());
        $cachetool->__call('doesNotExist', []);
    }

    public function testWithInexistentTempDir() {
        $dir = sys_get_temp_dir() . '/does-not-exist';

        $this->assertFalse(file_exists($dir));
        $cachetool = new CacheTool($dir);
        $this->assertTrue(file_exists($dir));

        rmdir($dir);
    }

    protected function getLogger()
    {
        return $this->getMockBuilder(\Monolog\Logger::class)->disableOriginalConstructor()->getMock();
    }
}
