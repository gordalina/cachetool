<?php

/*
 * This file is part of CacheTool.
 *
 * (c) Samuel Gordalina <samuel.gordalina@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
        $template =<<<'EOF'
$errors = array();

function cachetool_error_handler($errno, $errstr, $errfile, $errline) {
    global $errors;

    $errors[] = array(
        'no' => $errno,
        'str' => $errstr,
    );
}

function cachetool_exec() {
    global $errors;

    try {
        %s
    } catch (Exception $e) {
        $errors[] = array(
            'no' => $e->getCode(),
            'str' => $e->getMessage(),
        );
    }
}

set_error_handler('cachetool_error_handler');

echo serialize(array(
    'result' => cachetool_exec(),
    'errors' => $errors
));
EOF;

        return sprintf($template, $this->getCode());
    }
}
