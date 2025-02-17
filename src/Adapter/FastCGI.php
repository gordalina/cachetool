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
use CacheTool\Exception\RetryableException;
use hollodotme\FastCGI\Client;
use hollodotme\FastCGI\Interfaces\ConfiguresSocketConnection;
use hollodotme\FastCGI\SocketConnections\NetworkSocket;
use hollodotme\FastCGI\SocketConnections\UnixDomainSocket;
use hollodotme\FastCGI\Requests\PostRequest;

class FastCGI extends AbstractAdapter
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var ConfiguresSocketConnection
     */
    protected $connection;

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
            $last = strrpos($host, ':');
            $port = substr($host, $last + 1, strlen($host));
            $host = substr($host, 0, $last);

            $IPv6 = '/^(?:[A-F0-9]{0,4}:){1,7}[A-F0-9]{0,4}$/';
            if (preg_match($IPv6, $host) === 1) {
                // IPv6 addresses need to be surrounded by brackets
                // see: https://www.php.net/manual/en/function.stream-socket-client.php#refsect1-function.stream-socket-client-notes
                $host = "[{$host}]";
            }

            $this->connection = new NetworkSocket(
                $host,    # Hostname
                $port,    # Port
                5000,     # Connect timeout in milliseconds (default: 5000)
                120000    # Read/write timeout in milliseconds (default: 5000)
            );
        } else {
            $this->connection = new UnixDomainSocket(
                $host,  # Socket path to php-fpm
                5000,   # Connect timeout in milliseconds (default: 5000)
                120000  # Read/write timeout in milliseconds (default: 5000)
            );
        }

        $this->client = new Client();

        if ($chroot !== null) {
            $this->chroot = rtrim($chroot, '/');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function doRun(Code $code)
    {
        $body = $this->request($code);

        if (@unserialize($body) === false) {
            throw new \RuntimeException(sprintf("Error: %s", $body));
        }

        return $body;
    }

    protected function request(Code $code)
    {
        $file = $this->createTemporaryFile();
        $this->logger->info(sprintf('FastCGI: Dumped code to file: %s', $file));

        try {
            $code->writeTo($file);

            $this->logger->info(sprintf('FastCGI: Requesting FPM using socket: %s', $this->host));
            $request = new PostRequest($this->getScriptFileName($file), '');
            $response = $this->client->sendRequest($this->connection, $request);
            $this->logger->debug(sprintf('FastCGI: Response: %s', json_encode($response)));

            if (!@unlink($file)) {
                $this->logger->debug(sprintf('FastCGI: Could not delete file: %s', $file));
            }

            return $response->getBody();
        } catch (\Exception $e) {
            if (!@unlink($file)) {
                $this->logger->debug(sprintf('FastCGI: Could not delete file: %s', $file));
            }

            throw new RetryableException(
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
