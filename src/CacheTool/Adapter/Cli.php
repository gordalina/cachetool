<?php

namespace CacheTool\Adapter;

use CacheTool\Code;
use Symfony\Component\Process\Process;

class Cli implements AdapterInterface
{
    public function run(Code $code)
    {
        $file = sprintf("%s/cachetool-%s.php", sys_get_temp_dir(), uniqid());

        touch($file);
        chmod($file, 0666);

        $code->writeTo($file);

        $process = new Process("php $file");
        $process->run();

        @unlink($file);

        return unserialize($process->getOutput());
    }
}
