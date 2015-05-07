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
     * @var string
     */
    protected $host;

    /**
     * @param string $host 127.0.0.1:9000 or /var/run/php5-fpm.sock
     * @param string $tempDir
     */
    public function __construct($host = null, $tempDir = null)
    {
        // try to guess where it is
        if ($host === null) {
            if (file_exists('/var/run/php5-fpm.sock')) {
                $host = '/var/run/php5-fpm.sock';
            } else {
                $host = '127.0.0.1:9000';
            }
        }

        $this->host = $host;

        if (false !== strpos($host, ':')) {
            list($host, $port) = explode(':', $host);
            $this->client = new Client($host, $port);
        } else {
            // socket
            $this->client = new Client('unix://' . $host, -1);
        }

        $this->client->setReadWriteTimeout(60 * 1000);
        $this->client->setPersistentSocket(false);
        $this->client->setKeepAlive(true);
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

            $environment = array(
                'REQUEST_METHOD'  => 'POST',
                'REQUEST_URI'     => '/',
                'SCRIPT_FILENAME' => $file,
            );

            $response = $this->client->request($environment, '');
            $this->logger->debug(sprintf('FastCGI: Response: %s', json_encode($response)));

            @unlink($file);
            return $response;
        } catch (\Exception $e) {
            @unlink($file);

            throw new \RuntimeException(
                sprintf('FastCGI error: %s (%s)', $e->getMessage(), $this->host),
                $e->getCode()
            );
        }
    }
}
