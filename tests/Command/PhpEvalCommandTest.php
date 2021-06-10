<?php

namespace CacheTool\Command;

class PhpEvalCommandTest extends CommandTest
{
    public function testCommand()
    {
        $result = $this->runCommand('php:eval -r "return 1;" -v');

        $this->assertStringContainsString('_eval("return 1;")', $result);
        $this->assertStringContainsString("\n1\n", $result);
    }

    public function testFileCommand()
    {
        $file = tempnam(sys_get_temp_dir(), "tmp.php");
        file_put_contents($file, '<?php return 1;');

        $result = $this->runCommand("php:eval -f $file -v");

        $this->assertStringContainsString('_eval(" return 1;")', $result);
        $this->assertStringContainsString("\n1\n", $result);
    }

    public function testFailureCommand()
    {
        $result = $this->runCommand("php:eval -v");

        $this->assertStringContainsString('RuntimeException', $result);
    }

    public function testFailureNoFileCommand()
    {
        $result = $this->runCommand("php:eval -f does-not-exist -v");

        $this->assertStringContainsString('RuntimeException', $result);
    }
}
