<?php

namespace CacheTool\Proxy;

class OpcacheProxyTest extends ProxyTest
{
    public function testGetFunctions()
    {
        $this->assertCount(6, $this->createProxyInstance()->getFunctions());
    }

    public function testFunctions()
    {
        $this->assertProxyCode("return opcache_compile_file('file');", 'opcache_compile_file', array('file'));
        $this->assertProxyCode("return opcache_get_configuration();", 'opcache_get_configuration', array());
        $this->assertProxyCode("return opcache_get_status(true);", 'opcache_get_status', array(true));
        $this->assertProxyCode("return opcache_invalidate('key', true);", 'opcache_invalidate', array('key', true));
        $this->assertProxyCode("return opcache_reset();", 'opcache_reset', array());
        $this->assertProxyCode('return phpversion("Zend OPcache");', 'opcache_version', array());
    }

    protected function createProxyInstance()
    {
        return new OpcacheProxy();
    }
}
