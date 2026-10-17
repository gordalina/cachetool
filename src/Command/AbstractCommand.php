<?php

/*
 * This file is part of CacheTool.
 *
 * (c) Samuel Gordalina <samuel.gordalina@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CacheTool\Command;

use CacheTool\CacheTool;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractCommand extends Command
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @return CacheTool
     */
    protected function getCacheTool()
    {
        return $this->container->get('cachetool');
    }

    /**
     * @param  string $extension
     */
    protected function ensureExtensionLoaded($extension)
    {
        if (!$this->getCacheTool()->extension_loaded($extension)) {
            throw new \Exception("Extension `{$extension}` is not loaded");
        }
    }

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }
}
