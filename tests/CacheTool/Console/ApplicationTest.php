<?php

namespace CacheTool\Console;

use CacheTool\Command\CommandTest;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\BufferedOutput;

class ApplicationTest extends CommandTest
{
    public function atestVersion()
    {
        $result = $this->runCommand('--version');
        $this->assertEquals("CacheTool version @package_version@\n", $result);
    }

    public function testWithCli()
    {
        $app = new Application(new Config());
        $app->add(new DummyCommand);
        $app->setAutoExit(false);

        $code = $app->run(new StringInput("dummy --cli"), new NullOutput());
        $this->assertSame(42, $code);
    }

    public function testWithFcgi()
    {
        $app = new Application(new Config());
        $app->add(new DummyCommand);
        $app->setAutoExit(false);

        $code = $app->run(new StringInput("dummy --fcgi=127.0.0.1:9000"), new NullOutput());
        $this->assertSame(42, $code);
    }

    public function testWrongAdapter()
    {
        $app = new Application(new Config(array('adapter' => 'err')));
        $app->add(new DummyCommand);
        $app->setAutoExit(false);

        $output = new BufferedOutput();
        $code = $app->run(new StringInput("dummy"), $output);

        $this->assertSame(1, $code);
        $this->assertContains('Adapter `err` is not one of cli, fastcgi or web', $output->fetch());
    }

    public function testOutput()
    {
        $app = new Application(new Config(array('adapter' => 'cli')));
        $app->add(new DummyCommand);
        $app->setAutoExit(false);

        $output = new BufferedOutput();
        $code = $app->run(new StringInput("dummy -vvv"), $output);

        $this->assertSame(42, $code);
    }

    public function testNoSupportedExtensions()
    {
        $app = new Application(new Config(array('extensions' => [])));
        $app->setAutoExit(false);

        $output = new BufferedOutput();
        $code = $app->run(new StringInput("list"), $output);
        $content = $output->fetch();

        $this->assertSame(0, $code);
        $this->assertContains('stat:clear', $content);
        $this->assertNotContains('apc:bin:dump', $content);
        $this->assertNotContains('apcu:cache:clear', $content);
        $this->assertNotContains('opcache:configuration', $content);
    }

    public function testAllSupportedExtensions()
    {
        $app = new Application(new Config(array('extensions' => ['apc', 'apcu', 'opcache'])));
        $app->setAutoExit(false);

        $output = new BufferedOutput();
        $code = $app->run(new StringInput("list"), $output);
        $content = $output->fetch();

        $this->assertSame(0, $code);
        $this->assertContains('stat:clear', $content);
        $this->assertContains('apc:bin:dump', $content);
        $this->assertContains('apcu:cache:clear', $content);
        $this->assertContains('opcache:configuration', $content);
    }
}
