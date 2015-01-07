<?php

namespace CacheTool;

use CacheTool\Code;

class CodeTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $code = Code::fromString('return true;');
        $this->assertSame('return true;', $code->getCode());
    }

    public function testAddStatement()
    {
        $code = new Code();
        $code->addStatement('$a = 10;');
        $this->assertSame('$a = 10;', $code->getCode());

        $code->addStatement('return $a;');
        $this->assertSame("\$a = 10;\nreturn \$a;", $code->getCode());
    }

    public function testWriteTo()
    {
        $file = $this->createFile();

        $code = Code::fromString('$a = 10; return $a;');
        $code->writeTo($file);

        $this->assertContains('$a = 10; return $a;', file_get_contents($file));

        @unlink($file);
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Could not write to `/non-existant-folder/file.php`: No such file or directory.
     */
    public function testWriteToWrongFolder()
    {
        $file = '/non-existant-folder/file.php';

        $code = Code::fromString('$a = 10; return $a;');
        $code->writeTo($file);
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessageRegExp /Aborted due to security constraints: After writing to `.*` the contents were not the same/
     */
    public function testWriteToTampered()
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('HHVM does not support php://filter stream wrapper');
        }

        $file = sprintf("php://filter/write=string.rot13/resource=%s", $this->createFile());

        $code = Code::fromString('$a = 10; return $a;');
        $code->writeTo($file);
    }

    public function testCodeExecutable()
    {
        $code = Code::fromString('$a = 10; return $a;');
        $exec = $code->getCodeExecutable();

        ob_start();
        eval($exec);
        return ob_get_clean();

        $this->assertTrue(strlen($result) > 0);

        $unserialized = @unserialize($result);
        $this->assertTrue(is_array($unserialized));

        $this->assertCount(2, $unserialized);
        $this->assertArrayHasKey('result', $unserialized);
        $this->assertArrayHasKey('errors', $unserialized);

        $this->assertSame(10, $unserialized['result']);
        $this->assertTrue(is_array($unserialized['errors']));
        $this->assertCount(0, $unserialized['errors']);
    }

    public function testException()
    {
        $result = $this->getCodeResult('throw new \Exception("exception", 132); return true;');

        $this->assertNull($result['result']);
        $this->assertCount(1, $result['errors']);
        $this->assertSame(132, $result['errors'][0]['no']);
        $this->assertSame('exception', $result['errors'][0]['str']);
    }

    /**
     * @RunsInSeperateProcess
     */
    public function testNotice()
    {
        error_reporting(E_ALL);

        $result = $this->getCodeResult('strlen($foo); return true;');

        $this->assertTrue($result['result']);
        $this->assertCount(1, $result['errors']);
        $this->assertSame(E_NOTICE, $result['errors'][0]['no']);
        $this->assertSame("Undefined variable: foo", $result['errors'][0]['str']);
    }

    /**
     * @RunsInSeperateProcess
     */
    public function testWarning()
    {
        error_reporting(E_ALL);

        $result = $this->getCodeResult('strlen(array()); return true;');

        $this->assertTrue($result['result']);
        $this->assertCount(1, $result['errors']);
        $this->assertSame(E_WARNING, $result['errors'][0]['no']);
        $this->assertSame("strlen() expects parameter 1 to be string, array given", $result['errors'][0]['str']);
    }

    public function testUserErrors()
    {
        $errors = array(
            'E_USER_NOTICE',
            'E_USER_WARNING',
            'E_USER_ERROR',
            'E_USER_DEPRECATED',
        );

        foreach ($errors as $error) {
            $result = $this->getCodeResult(sprintf('trigger_error("%s", %s); return true;', $error, $error));

            $this->assertTrue($result['result']);
            $this->assertCount(1, $result['errors']);
            $this->assertSame(constant($error), $result['errors'][0]['no']);
            $this->assertSame($error, $result['errors'][0]['str']);
        }
    }

    protected function createFile()
    {
        $file = sprintf("%s/test-cachetool-%s.php", sys_get_temp_dir(), uniqid());

        touch($file);

        return $file;
    }

    protected function getCodeResult($statements)
    {
        $code = Code::fromString($statements);
        $exec = $code->getCodeExecutable();

        ob_start();
        eval($exec);

        $result = ob_get_clean();

        $this->assertTrue(strlen($result) > 0);

        $unserialized = unserialize($result);
        $this->assertTrue(is_array($unserialized));

        $this->assertCount(2, $unserialized);
        $this->assertArrayHasKey('result', $unserialized);
        $this->assertArrayHasKey('errors', $unserialized);

        return $unserialized;
    }
}
