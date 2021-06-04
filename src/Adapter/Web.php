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

use CacheTool\Adapter\Http\HttpInterface;
use CacheTool\Code;

class Web extends AbstractAdapter
{
    private $path;
    private $http;

    public function __construct($path, HttpInterface $http)
    {
        $this->path = realpath($path);
        $this->http = $http;
    }

    /**
     * {@inheritdoc}
     */
    protected function doRun(Code $code)
    {
        $filename = $this->createFilename();
        $file = $this->createWebFile($filename);
        $code->writeTo($file);

        $content = $this->http->fetch($filename);

        if (!@unlink($file)) {
            $this->logger->debug(sprintf('Web: Could not delete file: %s', $file));
        }

        return $content;
    }

    /**
     * @param string $filename
     * @return string
     */
    protected function createWebFile($filename)
    {
        $file = sprintf("%s/%s", $this->path, $filename);

        if (!touch($file)) {
            throw new \RuntimeException(sprintf("Could not create file: %s", $filename));
        }

        if (!chmod($file, 0664)) {
            throw new \RuntimeException(sprintf("Could not chmod file: %s", $filename));
        }

        return $file;
    }

    /**
     * @return string
     */
    protected function createFilename()
    {
        return sprintf('cachetool-%s.php', uniqid('', true));
    }
}
