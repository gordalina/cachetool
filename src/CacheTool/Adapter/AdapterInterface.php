<?php

namespace CacheTool\Adapter;

use CacheTool\Code;

interface AdapterInterface
{
    public function run(Code $code);
}
