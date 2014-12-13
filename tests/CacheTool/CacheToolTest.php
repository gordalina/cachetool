<?php

namespace CacheTool;

use CacheTool\CacheTool;
use CacheTool\Adapter;
use Monolog\Logger;

class CacheToolTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $cachetool = new CacheTool();
    }

    public function testFactory()
    {
        $cachetool = CacheTool::factory(null, $this->getLogger());

        $this->assertCount(3, $cachetool->getProxies());
        $this->assertNull($cachetool->getAdapter());
    }

    public function testFactoryWithAdapter()
    {
        $adapter = new Adapter\FastCGI();
        $cachetool = CacheTool::factory($adapter, $this->getLogger());

        $this->assertCount(3, $cachetool->getProxies());
        $this->assertSame($adapter, $cachetool->getAdapter());
    }

    public function testFactoryWithAdapterAndLogger()
    {
        $adapter = new Adapter\FastCGI();
        $logger = $this->getLogger();
        $cachetool = CacheTool::factory($adapter, $logger);

        $this->assertCount(3, $cachetool->getProxies());
        $this->assertSame($adapter, $cachetool->getAdapter());
        $this->assertSame($logger, $cachetool->getLogger());
    }

    public function testLoggerWithAdapter()
    {
        $cachetool = new CacheTool($this->getLogger());
        $cachetool->setAdapter(new Adapter\Cli);
        $cachetool->setLogger($this->getLogger());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInexistentFunction()
    {
        $cachetool = new CacheTool($this->getLogger());
        $cachetool->doesNotExist();
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInexistentWithMagicCallFunction()
    {
        $cachetool = new CacheTool($this->getLogger());
        $cachetool->__call('doesNotExist', array());
    }

    protected function getLogger()
    {
        return $this->getMockBuilder('Monolog\Logger')->disableOriginalConstructor()->getMock();
    }
}
