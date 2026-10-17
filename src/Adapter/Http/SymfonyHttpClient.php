<?php

/*
 * This file is part of CacheTool.
 *
 * (c) Samuel Gordalina <samuel.gordalina@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CacheTool\Adapter\Http;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;

class SymfonyHttpClient extends AbstractHttp
{
    private $client;

    public function __construct($baseUrl, $httpClientConfig = [])
    {
        $this->client = HttpClient::create($httpClientConfig);
        parent::__construct($baseUrl);
    }

    public function fetch($filename)
    {
        $url = "{$this->baseUrl}/{$filename}";

        if (!parse_url($url, PHP_URL_HOST)) {
            throw new \RuntimeException(
                sprintf(
                    "The given url is not valid: %s, did you forget to specify the --web-url option?",
                    $url
                )
            );
        }

        $response = $this->client->request('GET', $url);

        if ($response->getStatusCode() !== Response::HTTP_OK) {
            throw new \RuntimeException(
                sprintf(
                    "HTTP Response Code for URL %s is not 200, it is: %s",
                    $url,
                    $response->getStatusCode()
                )
            );
        }

        return $response->getContent();
    }
}
