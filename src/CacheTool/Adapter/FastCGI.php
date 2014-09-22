<?php

namespace CacheTool\Adapter;

use CacheTool\Code;
use EBernhardson\FastCGI\Client;
use EBernhardson\FastCGI\CommunicationException;

class FastCGI implements AdapterInterface
{
    protected $client;

    public function __construct($host)
    {
        if (false !== strpos($host, ':')) {
            list($host, $port) = explode(':', $host);
            $this->client = new Client($host, $port);
        } else {
            // socket
            $this->client = new Client($host);
        }
    }

    public function run(Code $code)
    {
        $response = $this->request($code);

        if ($response['statusCode'] !== 200) {
            throw new \RuntimeException($response['stderr']);
        } else {
            return unserialize($response['body']);
        }
    }

    protected function request(Code $code)
    {
        $file = sprintf("%s/cachetool-%s.php", sys_get_temp_dir(), uniqid());
        touch($file);
        chmod($file, 0666);

        try {
            $code->writeTo($file);

            $environment = array(
                'REQUEST_METHOD'  => 'POST',
                'REQUEST_URI'     => '/',
                'SCRIPT_FILENAME' => $file,
            );

            $this->client->request($environment, '');
            $response = $this->client->response();

            // lets close every request
            $this->client->close();

            @unlink($file);
            return $response;
        } catch (CommunicationException $e) {
            @unlink($file);

            throw new \RuntimeException(
                sprintf('Could not connect to FastCGI server: %s', $e->getMessage()),
                $e->getCode(),
                $e
            );
        }
    }
}
