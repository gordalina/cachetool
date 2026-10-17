<?php

namespace CacheTool\Monolog;

use Monolog\Level;
use Monolog\Logger;
use Monolog\LogRecord;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleHandlerTest extends \PHPUnit\Framework\TestCase
{
    public function testCustomVerbosityLevelMap()
    {
        $output = new BufferedOutput(OutputInterface::VERBOSITY_VERBOSE);
        $logger = new Logger('test', [new ConsoleHandler($output, true, [
            OutputInterface::VERBOSITY_VERBOSE => Logger::ERROR,
        ])]);

        $logger->warning('warning');
        $logger->error('error');

        $this->assertStringNotContainsString('warning', $output->fetch());
    }

    public function testVerbosityMissingFromMapLogsEverything()
    {
        $output = new BufferedOutput(OutputInterface::VERBOSITY_DEBUG);
        $logger = new Logger('test', [new ConsoleHandler($output, true, [
            OutputInterface::VERBOSITY_NORMAL => Logger::ERROR,
        ])]);

        $logger->debug('debug');

        $this->assertStringContainsString('debug', $output->fetch());
    }

    public function testSilentAndQuietDisableHandling()
    {
        $record = new LogRecord(new \DateTimeImmutable(), 'test', Level::Error, 'error');

        foreach ([OutputInterface::VERBOSITY_SILENT, OutputInterface::VERBOSITY_QUIET] as $verbosity) {
            $handler = new ConsoleHandler(new BufferedOutput($verbosity));
            $this->assertFalse($handler->isHandling($record));
        }
    }

    public function testErrorsGoToErrorOutput()
    {
        $errorOutput = new BufferedOutput();
        $output = $this->createMock(ConsoleOutputInterface::class);
        $output->method('getVerbosity')->willReturn(OutputInterface::VERBOSITY_NORMAL);
        $output->method('getErrorOutput')->willReturn($errorOutput);
        $output->expects($this->never())->method('write');

        $logger = new Logger('test', [new ConsoleHandler($output)]);
        $logger->error('error');

        $this->assertStringContainsString('test.ERROR: error', $errorOutput->fetch());
    }
}
