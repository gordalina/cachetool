<?php

namespace CacheTool;

class Code
{
    protected $code = array();

    public static function fromString($statement)
    {
        $code = new static();
        $code->addStatement($statement);

        return $code;
    }

    public function addStatement($statement)
    {
        $this->code[] = $statement;
    }

    public function getCode()
    {
        return implode(PHP_EOL, $this->code);
    }

    public function writeTo($file)
    {
        file_put_contents($file, '<?php' . PHP_EOL . $this->getCodeExecutable());
    }

    protected function getCodeExecutable()
    {
        return <<<EOF
function cachetool_exec() {
    {$this->getCode()}
}

echo serialize(cachetool_exec());
EOF;
    }
}
