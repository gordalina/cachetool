<?php

namespace CacheTool\Command;

class ApcuSmaInfoCommandTest extends CommandTest
{
    public function testCommand()
    {
        $this->assertHasApcu();

        $result = $this->runCommand('apcu:sma:info -v');

        $this->assertStringContainsString('apcu_sma_info(true)', $result);
        $this->assertStringContainsString('Segments', $result);
        $this->assertStringContainsString('Segment size', $result);
        $this->assertStringContainsString('Available memory', $result);
    }

    public function testJson()
    {
        $result = $this->runCommand('apcu:sma:info --format=json', ['num_seg' => 1, 'seg_size' => 2048, 'avail_mem' => 1024]);

        $this->assertSame(['segments' => 1, 'segment_size' => 2048, 'available_memory' => 1024], json_decode($result, true));
    }
}
