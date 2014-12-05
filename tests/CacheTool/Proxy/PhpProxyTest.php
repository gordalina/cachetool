<?php

namespace CacheTool\Proxy;

class PhpProxyTest extends ProxyTest
{
    public function testGetFunctions()
    {
        $this->assertCount(5, $this->createProxyInstance()->getFunctions());
    }

    public function testFunctions()
    {
        $this->assertProxyCode("return extension_loaded('ext');", 'extension_loaded', array('ext'));
        $this->assertProxyCode("return ini_get('var');", 'ini_get', array('var'));
        $this->assertProxyCode("return ini_set('var', 'value');", 'ini_set', array('var', 'value'));
        $this->assertProxyCode("return phpversion('php');", 'phpversion', array('php'));
        $this->assertProxyCode("passthru", '_eval', array('passthru'));
    }

    protected function createProxyInstance()
    {
        return new PhpProxy();
    }
}
