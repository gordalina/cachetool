<?php

namespace CacheTool\Proxy;

class ApcuProxyTest extends ProxyTest
{
    public function testGetFunctions()
    {
        $this->assertCount(14, $this->createProxyInstance()->getFunctions());
    }

    public function testApcAddException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $proxy = $this->createProxyInstance();
        $proxy->apcu_add('key', null);
    }

    public function testFunctions()
    {
        $this->assertProxyCode("return apcu_add('key', 'var', 0);", 'apcu_add', ['key', 'var', 0]);
        $this->assertProxyCode("return apcu_add(array (\n  'array_key' => 'array_var',\n), NULL, 0);", 'apcu_add', [['array_key' => 'array_var'], null, 0]);
        $this->assertProxyCode("return apcu_cas('key', 'old', 'new');", 'apcu_cas', ['key', 'old', 'new']);
        $this->assertProxyCode("return apcu_cache_info(false);", 'apcu_cache_info', [false]);
        $this->assertProxyCode("return apcu_clear_cache();", 'apcu_clear_cache', []);
        $this->assertProxyCode("return apcu_delete('key');", 'apcu_delete', ['key']);
        $this->assertProxyCode("return apcu_exists('keys');", 'apcu_exists', ['keys']);
        $this->assertProxyCode("return apcu_sma_info(false);", 'apcu_sma_info', [false]);
        $this->assertProxyCode("return apcu_store('key', 'var', 0);", 'apcu_store', ['key', 'var', 0]);
        $this->assertProxyCode("return apcu_store(array (\n  'array_key' => 'array_var',\n), NULL, 0);", 'apcu_store', [['array_key' => 'array_var'], null, 0]);
        $this->assertProxyCode('return phpversion("apcu");', 'apcu_version', []);
        $this->assertProxyCode('return iterator_to_array(new \APCUIterator(\'/test/\', APC_ITER_ALL, 10));', 'apcu_regexp_get_keys', ['/test/']);
        $this->assertProxyCode('return iterator_to_array(new \APCUIterator(NULL, APC_ITER_ALL, 10));', 'apcu_regexp_get_keys', []);
        $this->assertProxyCode("\$keys = new \APCUIterator('/test/', APC_ITER_KEY, 10);\nreturn apcu_delete(\$keys);", 'apcu_regexp_delete', ['/test/']);
        $this->assertProxyCode("\$keys = new \APCUIterator(NULL, APC_ITER_KEY, 10);\nreturn apcu_delete(\$keys);", 'apcu_regexp_delete', []);
    }

    public function testFunctionsArray()
    {
        $this->assertProxyCodeArray("\$success = false;\n\$result = apcu_dec('key', 'step', \$success);\nreturn array(\$result, \$success);", 'apcu_dec', ['key', 'step', new \stdClass]);
        $this->assertProxyCodeArray("\$success = false;\n\$result = apcu_fetch('key', \$success);\nreturn array(\$result, \$success);", 'apcu_fetch', ['key', new \stdClass]);
        $this->assertProxyCodeArray("\$success = false;\n\$result = apcu_inc('key', 'step', \$success);\nreturn array(\$result, \$success);", 'apcu_inc', ['key', 'step', new \stdClass]);
    }

    public function testInvalidRegexGetKeys()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->assertFailedProxyCode('', 'apcu_regexp_get_keys', ['invalid']);
    }

    public function testInvalidRegexDelete()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->assertFailedProxyCode('', 'apcu_regexp_delete', ['invalid']);
    }

    protected function createProxyInstance()
    {
        return new ApcuProxy();
    }
}
