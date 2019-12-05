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
        $this->assertInstanceOf('CacheTool\CacheTool', $cachetool);
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

    public function testFactoryWithTempDirNotWritable()
    {
        $adapter = new Adapter\FastCGI();
        $tempDir = '/doesnotexit';
        $cachetool = CacheTool::factory($adapter, $tempDir, $this->getLogger());
        $this->assertSame($tempDir, $cachetool->getTempDir());
    }
    
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInexistentFunction()
    {
        $cachetool = new CacheTool(null, $this->getLogger());
        $cachetool->doesNotExist();
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInexistentWithMagicCallFunction()
    {
        $cachetool = new CacheTool(null, $this->getLogger());
        $cachetool->__call('doesNotExist', []);
    }

    protected function getLogger()
    {
        return $this->getMockBuilder('Monolog\Logger')->disableOriginalConstructor()->getMock();
    }
}
