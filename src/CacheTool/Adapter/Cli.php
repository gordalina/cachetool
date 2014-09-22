<?php

namespace CacheTool\Adapter;

use CacheTool\Code;

class Cli implements AdapterInterface
{
    public function run(Code $code)
    {
        return $code->execute();
    }
}
