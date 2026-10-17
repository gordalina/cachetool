<?php

namespace CacheTool\Console;

use CacheTool\Command\CommandTest;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\BufferedOutput;
use PHPUnit\Framework\Constraint\RegularExpression;

class ApplicationTest extends CommandTest
{
    public function atestVersion()
    {
        $result = $this->runCommand('--version');
        $this->assertSame("CacheTool version @package_version@\n", $result);
    }

    public function testWithCli()
    {
        $app = new Application(new Config());
        $app->add(new DummyCommand);
        $app->setAutoExit(false);

        $code = $app->run(new StringInput("dummy --cli"), new NullOutput());
        $this->assertSame(42, $code);
    }

    public function testRetry()
    {
        $app = new Application(new Config());

        $input = new StringInput('--cli');
        $input->bind($app->getDefinition());
        $this->assertSame(3, $app->buildContainer($input)->get('cachetool')->getAdapter()->getRetries());

        $input = new StringInput('--cli --retries=0');
        $input->bind($app->getDefinition());
        $this->assertSame(0, $app->buildContainer($input)->get('cachetool')->getAdapter()->getRetries());
    }

    public function timeoutProvider()
    {
        return [
            'cli' => ['--cli'],
            'fastcgi' => ['--fcgi=127.0.0.1:%d'],
            'FileGetContents' => ['--web=FileGetContents --web-url=http://127.0.0.1:%d --web-path=' . sys_get_temp_dir()],
            'SymfonyHttpClient' => ['--web=SymfonyHttpClient --web-url=http://127.0.0.1:%d --web-path=' . sys_get_temp_dir()],
        ];
    }

    /**
     * @dataProvider timeoutProvider
     */
    public function testTimeout($options)
    {
        // accepts connections but never replies
        $server = stream_socket_server('tcp://127.0.0.1:0');
        $port = (int) substr(strrchr(stream_socket_get_name($server, false), ':'), 1);

        $app = new Application(new Config());
        $input = new StringInput(sprintf($options, $port) . ' --timeout=0.5 --retries=0');
        $input->bind($app->getDefinition());
        $adapter = $app->buildContainer($input)->get('cachetool')->getAdapter();

        $start = microtime(true);
        try {
            $adapter->run(\CacheTool\Code::fromString('sleep(5); return true;'));
            $this->fail('Expected a timeout');
        } catch (\RuntimeException $e) {
            $this->assertLessThan(3, microtime(true) - $start, $e->getMessage());
        }
    }

    public function testInvalidTimeout()
    {
        $app = new Application(new Config());
        $input = new StringInput('--cli --timeout=0');
        $input->bind($app->getDefinition());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Timeout must be a number of seconds greater than 0');
        $app->buildContainer($input);
    }

    public function testWithFcgi()
    {
        $app = new Application(new Config());
        $app->add(new DummyCommand);
        $app->setAutoExit(false);

        $code = $app->run(new StringInput("dummy --fcgi=127.0.0.1:9000"), new NullOutput());
        $this->assertSame(42, $code);
    }

    public function testWebAdapter()
    {
        $app = new Application(new Config());
        $app->add(new DummyCommand);
        $app->setAutoExit(false);

        $output = new BufferedOutput();
        $code = $app->run(new StringInput("dummy --web"), $output);

        $this->assertSame(42, $code);
    }

    public function testWebFileGetContentsAdapter()
    {
        $app = new Application(new Config());
        $app->add(new DummyCommand);
        $app->setAutoExit(false);

        $output = new BufferedOutput();
        $code = $app->run(new StringInput("dummy --web=FileGetContents"), $output);

        $this->assertSame(42, $code);
    }

    public function testWebSymfonyHttpClientAdapter()
    {
        $app = new Application(new Config());
        $app->add(new DummyCommand);
        $app->setAutoExit(false);

        $output = new BufferedOutput();
        $code = $app->run(new StringInput("dummy --web=SymfonyHttpClient"), $output);

        $this->assertSame(42, $code);
    }

    public function testWrongAdapter()
    {
        $app = new Application(new Config(['adapter' => 'err']));
        $app->add(new DummyCommand);
        $app->setAutoExit(false);

        $output = new BufferedOutput();
        $code = $app->run(new StringInput("dummy"), $output);

        $this->assertSame(1, $code);
        $this->assertStringContainsString('Adapter `err` is not one of cli, fastcgi or web', $output->fetch());
    }

    public function testOutput()
    {
        $app = new Application(new Config(['adapter' => 'cli']));
        $app->add(new DummyCommand);
        $app->setAutoExit(false);

        $output = new BufferedOutput();
        $code = $app->run(new StringInput("dummy -vvv"), $output);

        $this->assertSame(42, $code);
    }

    public function testWithCustomConfigFile()
    {
        $temp = tempnam(sys_get_temp_dir(), "cfg");
        file_put_contents($temp, 'adapter: cli');

        $app = new Application(new Config(['adapter' => 'err']));
        $app->add(new DummyCommand);
        $app->setAutoExit(false);

        $output = new BufferedOutput();
        $code = $app->run(new StringInput("dummy --config=$temp"), $output);

        $this->assertSame(42, $code);
    }

    public function testWebSymfonyHttpClientCustomConfigFile()
    {
        $temp = tempnam(sys_get_temp_dir(), "cfg");
        file_put_contents($temp, '
adapter: web
webClient: SymfonyHttpClient
webUrl: http://example.com
webPath: /var/www/example.com/current/web
webBasicAuth: user:password
');

        $app = new Application(new Config());
        $app->add(new DummyCommand);
        $app->setAutoExit(false);

        $output = new BufferedOutput();
        $code = $app->run(new StringInput("dummy --config=$temp"), $output);

        $this->assertSame(42, $code);
    }

    public function testNoSupportedExtensions()
    {
        $app = new Application(new Config(['extensions' => []]));
        $app->setAutoExit(false);

        $output = new BufferedOutput();
        $code = $app->run(new StringInput("list"), $output);
        $content = $output->fetch();

        $this->assertSame(0, $code);
        $this->assertStringContainsString('stat:clear', $content);
        $this->assertStringNotContainsString('apcu:cache:clear', $content);
        $this->assertStringNotContainsString('opcache:configuration', $content);
    }

    public function testAllSupportedExtensions()
    {
        $app = new Application(new Config(['extensions' => ['apcu', 'opcache']]));
        $app->setAutoExit(false);

        $output = new BufferedOutput();
        $code = $app->run(new StringInput("list"), $output);
        $content = $output->fetch();

        $this->assertSame(0, $code);
        $this->assertStringContainsString('stat:clear', $content);
        $this->assertStringContainsString('apcu:cache:clear', $content);
        $this->assertStringContainsString('opcache:configuration', $content);
    }

    public function testWithInexistentConfigFile()
    {
        $app = new Application(new Config());
        $app->setAutoExit(false);

        $output = new BufferedOutput();
        $code = $app->run(new StringInput('opcache:reset --config=/doesnotexist'), $output);
        $content = $output->fetch();

        $this->assertSame(1, $code);
        $this->assertThat($content, new RegularExpression('|Could not read configuration file: /doesnotexist|'));
    }

    public function testWithTmpDirArgNotWritable()
    {
        $app = new Application(new Config());
        $app->setAutoExit(false);

        $output = new BufferedOutput();
        $code = $app->run(new StringInput('opcache:reset --tmp-dir=/doesnotexist'), $output);
        $content = $output->fetch();

        $this->assertSame(1, $code);
        $this->assertThat($content, new RegularExpression('|Could not write to `/doesnotexist/cachetool-.*\.php`:|'));
    }

    public function testWithTmpDirConfigNotWritable()
    {
        $app = new Application(new Config(['temp_dir' => '/doesnotexist']));
        $app->setAutoExit(false);

        $output = new BufferedOutput();
        $code = $app->run(new StringInput('opcache:reset'), $output);
        $content = $output->fetch();

        $this->assertSame(1, $code);
        $this->assertThat($content, new RegularExpression('|Could not write to `/doesnotexist/cachetool-.*\.php`:|'));
    }

    public function testWebSymfonyHttpClientInsecureWithHost()
    {
        $app = new Application(new Config());
        $app->add(new DummyCommand);
        $app->setAutoExit(false);

        $code = $app->run(new StringInput("dummy --web=SymfonyHttpClient --web-allow-insecure --web-host=example.com"), new NullOutput());

        $this->assertSame(42, $code);
    }

    public function testWebUnsupportedClient()
    {
        $app = new Application(new Config());
        $app->add(new DummyCommand);
        $app->setAutoExit(false);

        $output = new BufferedOutput();
        $code = $app->run(new StringInput("dummy --web=Unknown"), $output);

        $this->assertSame(42, $code);
        $this->assertStringContainsString('Web client `Unknown` not supported - defaulting to FileGetContents', $output->fetch());
    }
}
