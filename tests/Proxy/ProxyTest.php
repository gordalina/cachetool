<?php

namespace CacheTool\Proxy;

abstract class ProxyTest extends \PHPUnit\Framework\TestCase
{
    abstract protected function createProxyInstance();

    protected function assertProxyCode($code, $function, $arguments)
    {
        $mock = $this->createMock('CacheTool\Adapter\Cli');
        $mock->expects($this->once())
            ->method('run')
            ->willReturnArgument(0);

        $proxy = $this->createProxyInstance();
        $proxy->setAdapter($mock);

        $result = $proxy->$function(...$arguments);

        $this->assertSame($code, $result->getCode());
    }

    protected function assertProxyCodeArray($code, $function, $arguments)
    {
        $mock = $this->createMock('CacheTool\Adapter\Cli');
        $mock->expects($this->once())
            ->method('run')
            ->willReturnCallback(function ($code) {
                return [$code, true];
            });

        $proxy = $this->createProxyInstance();
        $proxy->setAdapter($mock);

        $result = $proxy->$function(...$arguments);

        $this->assertSame($code, $result->getCode());
    }

    protected function assertFailedProxyCode($code, $function, $arguments)
    {
        $mock = $this->createStub('CacheTool\Adapter\Cli');
        $proxy = $this->createProxyInstance();
        $proxy->setAdapter($mock);

        $result = $proxy->$function(...$arguments);

        $this->assertSame($code, $result->getCode());
    }
}
