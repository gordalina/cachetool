<?php

namespace CacheTool\Proxy;

class PhpProxyTest extends ProxyTest
{
    public function testGetFunctions()
    {
        $this->assertCount(8, $this->createProxyInstance()->getFunctions());
    }

    public function testFunctions()
    {
        $this->assertProxyCode("return extension_loaded('ext');", 'extension_loaded', ['ext']);
        $this->assertProxyCode("return ini_get('var');", 'ini_get', ['var']);
        $this->assertProxyCode("return ini_set('var', 'value');", 'ini_set', ['var', 'value']);
        $this->assertProxyCode("return phpversion('php');", 'phpversion', ['php']);
        $this->assertProxyCode("passthru", '_eval', ['passthru']);
        $this->assertProxyCode('return realpath_cache_get();', 'stat_realpath_get', []);
        $this->assertProxyCode('return realpath_cache_size();', 'stat_realpath_size', []);
        $this->assertProxyCode('return clearstatcache(true);', 'stat_cache_clear', []);
    }

    protected function createProxyInstance()
    {
        return new PhpProxy();
    }
}
