<?php

/*
 * This file is part of CacheTool.
 *
 * (c) Samuel Gordalina <samuel.gordalina@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CacheTool\Adapter;

use CacheTool\Code;
use Adoy\FastCGI\Client;

class FastCGI extends AbstractAdapter
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Array of patterns matching php socket files
     */
    protected $possibleSocketFilePatterns = [
        '/var/run/php*.sock',
        '/var/run/php/*.sock'
    ];

    /**
     * @var string
     */
    protected $host;

    /**
     * @var string
     */
    protected $chroot;

    /**
     * @param string $host 127.0.0.1:9000 or /var/run/php5-fpm.sock
     * @param string $chroot
     */
    public function __construct($host = null, $chroot = null)
    {
        // try to guess where it is
        if ($host === null) {
            foreach ($this->possibleSocketFilePatterns as $possibleSocketFilePattern) {
                $possibleSocketFile = current(glob($possibleSocketFilePattern));
                if (file_exists($possibleSocketFile)) {
                    $host = $possibleSocketFile;
                    break;
                }
            }
            if ($host === null) {
                $host = '127.0.0.1:9000';
            }
        }

        $this->host = $host;

        if (false !== strpos($host, ':')) {
            [$host, $port] = explode(':', $host);
            $this->client = new Client($host, $port);
        } else {
            // socket
            $this->client = new Client('unix://' . $host, -1);
        }

        $this->client->setReadWriteTimeout(60 * 1000);
        $this->client->setPersistentSocket(false);
        $this->client->setKeepAlive(true);

        if ($chroot !== null) {
            $this->chroot = rtrim($chroot, '/');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function doRun(Code $code)
    {
        $response = $this->request($code);
        $parts = explode("\r\n\r\n", $response);

        // remove headers
        array_shift($parts);
        $body = implode("\r\n\r\n", $parts);

        if (@unserialize($body) === false) {
            throw new \RuntimeException(sprintf("Error: %s", $response));
        }

        return $body;
    }

    protected function request(Code $code)
    {
        $file = $this->createTemporaryFile();
        $this->logger->info(sprintf('FastCGI: Dumped code to file: %s', $file));

        try {
            $code->writeTo($file);

            $environment = [
                'REQUEST_METHOD'  => 'POST',
                'REQUEST_URI'     => '/',
                'SCRIPT_FILENAME' => $this->getScriptFileName($file),
            ];

            $this->logger->info(sprintf('FastCGI: Requesting FPM using socket: %s', $this->host));
            $response = $this->client->request($environment, '');
            $this->logger->debug(sprintf('FastCGI: Response: %s', json_encode($response)));

            if (!@unlink($file)) {
                $this->logger->debug(sprintf('FastCGI: Could not delete file: %s', $file));
            }

            return $response;
        } catch (\Exception $e) {
            if (!@unlink($file)) {
                $this->logger->debug(sprintf('FastCGI: Could not delete file: %s', $file));
            }

            throw new \RuntimeException(
                sprintf('FastCGI error: %s (%s)', $e->getMessage(), $this->host),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * @param string $file
     * @return string
     * @throws \RuntimeException
     */
    protected function getScriptFileName($file)
    {
        if ($this->chroot) {
            if (substr($file, 0, strlen($this->chroot)) === $this->chroot) {
                return substr($file, strlen($this->chroot));
            }
            throw new \RuntimeException('FastCGI configured to be chrooted, but file not in chroot directory.');
        }
        return $file;
    }
}
