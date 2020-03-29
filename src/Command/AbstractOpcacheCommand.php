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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractOpcacheCommand extends AbstractCommand
{
    /**
     * @param array $info
     */
    protected function ensureSuccessfulOpcacheCall($info)
    {
        if ($info === false) {
           throw new \RuntimeException('opcache_get_status(): No Opcache status info available.  Perhaps Opcache is disabled via opcache.enable or opcache.enable_cli?');
        }

        if ($info['restart_pending'] ?? false) {
            $cacheStatus = $info['cache_full'] ? 'Also, you cache is full.' : '';
            throw new \RuntimeException("OPCache is restart, as such files can't be invalidated. Try again later. ${cacheStatus}");
        }

        $file_cache_only = $info['file_cache_only'] ?? false;
        $opcache_enabled = $info['opcache_enabled'] ?? false;

        if (!$opcache_enabled && $file_cache_only) {
            throw new \RuntimeException("Couldn't execute command because opcache is only functioning as a file cache. Set `opcache.file_cache_only` to false if you want to use this command. Read more at https://www.php.net/manual/en/opcache.configuration.php#ini.opcache.file-cache-only");
        }
    }
}
