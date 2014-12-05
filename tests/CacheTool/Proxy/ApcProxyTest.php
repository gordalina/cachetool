<?php

namespace CacheTool\Proxy;

use CacheTool\Code;

class ApcProxyTest extends ProxyTest
{
    public function testGetFunctions()
    {
        $this->assertCount(20, $this->createProxyInstance()->getFunctions());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testApcAddException()
    {
        $proxy = $this->createProxyInstance();
        $proxy->apc_add(array(), null);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testApcStoreException()
    {
        $proxy = $this->createProxyInstance();
        $proxy->apc_store(array(), null);
    }

    public function testFunctions()
    {
        $this->assertProxyCode("return apc_add('key', 'var', 0);", 'apc_add', array('key', 'var', 0));
        $this->assertProxyCode("return apc_bin_dump(NULL, NULL);", 'apc_bin_dump', array(NULL, NULL));
        $this->assertProxyCode("return apc_bin_dumpfile(array (\n), array (\n), 'filename', 0, NULL);", 'apc_bin_dumpfile', array(array(), array(), 'filename', 0, NULL));
        $this->assertProxyCode("return apc_bin_load('data', 0);", 'apc_bin_load', array('data', 0));
        $this->assertProxyCode("return apc_bin_loadfile('filename', 'context', 0);", 'apc_bin_loadfile', array('filename', 'context', 0));
        $this->assertProxyCode("return apc_cas('key', 'old', 'new');", 'apc_cas', array('key', 'old', 'new'));
        $this->assertProxyCode("return apc_cache_info('cache_type', false);", 'apc_cache_info', array('cache_type', false));
        $this->assertProxyCode("return apc_clear_cache('cache_type');", 'apc_clear_cache', array('cache_type'));
        $this->assertProxyCode("return apc_compile_file('filename', true);", 'apc_compile_file', array('filename', true));
        $this->assertProxyCode("return apc_define_constants('key', array (\n), true);", 'apc_define_constants', array('key', array(), true));
        $this->assertProxyCode("return apc_delete_file('keys');", 'apc_delete_file', array('keys'));
        $this->assertProxyCode("return apc_delete('key');", 'apc_delete', array('key'));
        $this->assertProxyCode("return apc_exists('keys');", 'apc_exists', array('keys'));
        $this->assertProxyCode("return apc_load_constants('key', true);", 'apc_load_constants', array('key', true));
        $this->assertProxyCode("return apc_sma_info(false);", 'apc_sma_info', array(false));
        $this->assertProxyCode("return apc_store('key', 'var', 0);", 'apc_store', array('key', 'var', 0));
        $this->assertProxyCode('return phpversion("apc");', 'apc_version', array());
    }

    public function testFunctionsArray()
    {
        $this->assertProxyCodeArray("\$success = false;\n\$result = apc_dec('key', 'step', \$success);\nreturn array(\$result, \$success);", 'apc_dec', array('key', 'step', new \stdClass));
        $this->assertProxyCodeArray("\$success = false;\n\$result = apc_fetch('key', \$success);\nreturn array(\$result, \$success);", 'apc_fetch', array('key', new \stdClass));
        $this->assertProxyCodeArray("\$success = false;\n\$result = apc_inc('key', 'step', \$success);\nreturn array(\$result, \$success);", 'apc_inc', array('key', 'step', new \stdClass));
    }

    protected function createProxyInstance()
    {
        return new ApcProxy();
    }
}
