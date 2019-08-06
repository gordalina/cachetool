<?php

namespace CacheTool;

use PHPUnit\Framework\Assert;
use Symfony\Component\Process\Process;

class PhpFpmRunner
{
    /**
     * @var string
     */
    public $socket;

    /**
     * @var process
     */
    private $process;

    public function __construct()
    {
        $process = new Process(['which', 'php-fpm']);
        $status = $process->run();
        if ($status) {
            Assert::markTestSkipped('PHP-FPM is not available.');
        }

        $this->socket = tempnam(sys_get_temp_dir(), 'cachetool');
        if (!$this->socket) {
            throw new \RuntimeException('Could not create temporary file.');
        }
        unlink($this->socket);
        $this->socket .= '.sock';

        $this->process = new Process(
            [
                'php-fpm',
                '--nodaemonize',
                '--fpm-config',
                __DIR__ . '/php-fpm.conf',
            ],
            null,
            ['PHP_FPM_LISTEN' => $this->socket]
        );
        $this->process->start();

        // Wait for socket to be ready.
        while (!file_exists($this->socket));
    }

    public function __destruct()
    {
        $this->process->stop();
    }
}
