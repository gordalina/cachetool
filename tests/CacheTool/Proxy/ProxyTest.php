<?php

namespace CacheTool\Proxy;

abstract class ProxyTest extends \PHPUnit_Framework_TestCase
{
    abstract protected function createProxyInstance();

    protected function assertProxyCode($code, $function, $arguments)
    {
        $mock = $this->getMock('CacheTool\Adapter\Cli');
        $mock->expects($this->once())
            ->method('run')
            ->will($this->returnArgument(0));

        $proxy = $this->createProxyInstance();
        $proxy->setAdapter($mock);

        $result = call_user_func_array(array($proxy, $function), $arguments);

        $this->assertSame($code, $result->getCode());
    }

    protected function assertProxyCodeArray($code, $function, $arguments)
    {
        $mock = $this->getMock('CacheTool\Adapter\Cli');
        $mock->expects($this->once())
            ->method('run')
            ->will($this->returnCallback(function ($code) {
                return array($code);
            }));

        $proxy = $this->createProxyInstance();
        $proxy->setAdapter($mock);

        $result = call_user_func_array(array($proxy, $function), $arguments);

        $this->assertSame($code, $result->getCode());
    }
}
