<?php

namespace CacheTool\Proxy;

class ApcuProxyTest extends ProxyTest
{
    public function testGetFunctions()
    {
        $this->assertCount(12, $this->createProxyInstance()->getFunctions());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testApcAddException()
    {
        $proxy = $this->createProxyInstance();
        $proxy->apcu_add('key', null);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testApcStoreException()
    {
        $proxy = $this->createProxyInstance();
        $proxy->apcu_store('key', null);
    }

    public function testFunctions()
    {
        $this->assertProxyCode("return apcu_add('key', 'var', 0);", 'apcu_add', array('key', 'var', 0));
        $this->assertProxyCode("return apcu_add(array (\n  'array_key' => 'array_var',\n), NULL, 0);", 'apcu_add', array(array('array_key' => 'array_var'), null, 0));
        $this->assertProxyCode("return apcu_cas('key', 'old', 'new');", 'apcu_cas', array('key', 'old', 'new'));
        $this->assertProxyCode("return apcu_cache_info(false);", 'apcu_cache_info', array(false));
        $this->assertProxyCode("return apcu_delete('key');", 'apcu_delete', array('key'));
        $this->assertProxyCode("return apcu_exists('keys');", 'apcu_exists', array('keys'));
        $this->assertProxyCode("return apcu_sma_info(false);", 'apcu_sma_info', array(false));
        $this->assertProxyCode("return apcu_store('key', 'var', 0);", 'apcu_store', array('key', 'var', 0));
        $this->assertProxyCode("return apcu_store(array (\n  'array_key' => 'array_var',\n), NULL, 0);", 'apcu_store', array(array('array_key' => 'array_var'), null, 0));
        $this->assertProxyCode('return phpversion("apcu");', 'apcu_version', array());
    }

    public function testFunctionsArray()
    {
        $this->assertProxyCodeArray("\$success = false;\n\$result = apcu_dec('key', 'step', \$success);\nreturn array(\$result, \$success);", 'apcu_dec', array('key', 'step', new \stdClass));
        $this->assertProxyCodeArray("\$success = false;\n\$result = apcu_fetch('key', \$success);\nreturn array(\$result, \$success);", 'apcu_fetch', array('key', new \stdClass));
        $this->assertProxyCodeArray("\$success = false;\n\$result = apcu_inc('key', 'step', \$success);\nreturn array(\$result, \$success);", 'apcu_inc', array('key', 'step', new \stdClass));
    }

    protected function createProxyInstance()
    {
        return new ApcuProxy();
    }
}
