<?php

namespace CacheTool\Command;

class OpcacheConfigurationCommandTest extends CommandTest
{
    public function testCommand()
    {
        $this->assertHasOpcache();

        $result = $this->runCommand('opcache:configuration -v');

        $this->assertStringContainsString('opcache_get_configuration()', $result);
        $this->assertStringContainsString('Directive', $result);
        $this->assertStringContainsString('Value', $result);
        $this->assertStringContainsString('opcache.enable', $result);
        $this->assertStringContainsString('opcache.enable_cli', $result);
    }

    public function testJsonHasRawValuesAndNoVersionLine()
    {
        $result = $this->runCommand('opcache:configuration --format=json', [
            'version' => ['opcache_product_name' => 'Zend OPcache', 'version' => '8.4.0'],
            'directives' => ['opcache.enable' => true, 'opcache.memory_consumption' => 134217728],
        ]);

        $this->assertSame([
            ['directive' => 'opcache.enable', 'value' => true],
            ['directive' => 'opcache.memory_consumption', 'value' => 134217728],
        ], json_decode($result, true));
    }
}
