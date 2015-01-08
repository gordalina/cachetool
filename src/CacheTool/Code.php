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
    /**
     * @var array
     */
    protected $code = array();

    /**
     * @param  string $statement
     * @return Code
     */
    public static function fromString($statement)
    {
        $code = new static();
        $code->addStatement($statement);

        return $code;
    }

    /**
     * @param string $statement
     */
    public function addStatement($statement)
    {
        $this->code[] = $statement;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return implode(PHP_EOL, $this->code);
    }

    /**
     * @param  string $file
     * @return null
     */
    public function writeTo($file)
    {
        $code = '<?php' . PHP_EOL . $this->getCodeExecutable();
        $chksum = md5($code);

        if (false === @file_put_contents($file, $code)) {
            throw new \RuntimeException("Could not write to `{$file}`: No such file or directory.");
        }

        if ($chksum !== md5_file($file)) {
            throw new \RuntimeException("Aborted due to security constraints: After writing to `{$file}` the contents were not the same.");
        }
    }

    /**
     * @return string
     */
    public function getCodeExecutable()
    {
        $template =<<<'EOF'
$errors = array();

$cachetool_error_handler_%s = function($errno, $errstr, $errfile, $errline) use (&$errors) {
    $errors[] = array(
        'no' => $errno,
        'str' => $errstr,
    );
};

$cachetool_exec_%s = function() use (&$errors) {
    try {
        %s
    } catch (\Exception $e) {
        $errors[] = array(
            'no' => $e->getCode(),
            'str' => $e->getMessage(),
        );
    }
};

set_error_handler($cachetool_error_handler_%s);

$result = $cachetool_exec_%s();

echo serialize(array(
    'result' => $result,
    'errors' => $errors
));
EOF;

        $uniq = uniqid();

        return sprintf($template, $uniq, $uniq, $this->getCode(), $uniq, $uniq);
    }
}
