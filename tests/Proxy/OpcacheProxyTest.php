<?php

namespace CacheTool\Proxy;

class OpcacheProxyTest extends ProxyTest
{
    public function testGetFunctions()
    {
        $this->assertCount(7, $this->createProxyInstance()->getFunctions());
    }

    public function testFunctions()
    {
        $this->assertProxyCode("return opcache_compile_file('file');", 'opcache_compile_file', ['file']);
        $this->assertProxyCode(join(PHP_EOL, [
            '$paths = [',
            "    'file1',",
            "    'file2',",
            '];',
            'foreach ($paths as $path) {',
            '    $compiled = opcache_compile_file($path);',
            '    if (!$compiled) {',
            '        return false;',
            '    }',
            '}',
            'return true;'
        ]), 'opcache_compile_files', [['file1', 'file2']]);
        $this->assertProxyCode("return opcache_get_configuration();", 'opcache_get_configuration', []);
        $this->assertProxyCode("return opcache_get_status(true);", 'opcache_get_status', [true]);
        $this->assertProxyCode("return opcache_invalidate('key', true);", 'opcache_invalidate', ['key', true]);
        $this->assertProxyCode("opcache_reset();\nreturn true;", 'opcache_reset', []);
        $this->assertProxyCode('return phpversion("Zend OPcache");', 'opcache_version', []);
    }

    protected function createProxyInstance()
    {
        return new OpcacheProxy();
    }
}
