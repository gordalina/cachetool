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

use CacheTool\Util\Formatter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OpcacheStatusCommand extends AbstractOpcacheCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('opcache:status')
            ->setDescription('Show summary information about the opcode cache')
            ->addFormatOption()
            ->setHelp('');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->ensureExtensionLoaded('Zend OPcache');

        $info = $this->getCacheTool()->opcache_get_status(false);
        try {
            $this->ensureSuccessfulOpcacheCall($info);
        } catch (\RuntimeException $e) {
            // lets handle gracefully when file_cache_only is true
            if (!strstr($e->getMessage(), 'opcache.file_cache_only')) {
                throw $e;
            }
        }

        if ($info['file_cache_only'] ?? false) {
            $fields = $this->getFileCacheOnlyFields($info);
        } else {
            $fields = $this->getFields($info, $info['opcache_statistics']);
        }

        $this->renderRecord($input, $output, $fields);

        return 0;
    }

    /**
     * @param  array $info
     * @return array
     */
    protected function getFileCacheOnlyFields($info)
    {
        $fields = [];

        foreach ($info as $key => $value) {
            $fields[$key] = [$key, $value, fn ($value) => var_export($value, true)];
        }

        return $fields;
    }

    /**
     * @param  array $info
     * @param  array $stats
     * @return array
     */
    protected function getFields($info, $stats)
    {
        $fields = $this->getGeneralFields($info);

        if (isset($info['interned_strings_usage'])) {
            $fields += $this->getStringsFields($info);
        }

        return $fields + $this->getOpcacheStatsFields($stats);
    }

    /**
     * @param  array $info
     * @return array
     */
    protected function getGeneralFields($info)
    {
        return [
            'enabled' => ['Enabled', $info['opcache_enabled'], Formatter::bool(...)],
            'cache_full' => ['Cache full', $info['cache_full'], Formatter::bool(...)],
            'restart_pending' => ['Restart pending', $info['restart_pending'], Formatter::bool(...)],
            'restart_in_progress' => ['Restart in progress', $info['restart_in_progress'], Formatter::bool(...)],
            'memory_used' => ['Memory used', $info['memory_usage']['used_memory'], Formatter::bytes(...)],
            'memory_free' => ['Memory free', $info['memory_usage']['free_memory'], Formatter::bytes(...)],
            'memory_wasted' => ['Memory wasted', $info['memory_usage']['wasted_memory'], Formatter::bytes(...)],
            'memory_wasted_percentage' => ['Memory wasted (%)', $info['memory_usage']['current_wasted_percentage'], fn ($percentage) => "{$percentage}%"],
        ];
    }

    /**
     * @param  array $info
     * @return array
     */
    protected function getStringsFields($info)
    {
        return [
            'strings_buffer_size' => ['Strings buffer size', $info['interned_strings_usage']['buffer_size'], Formatter::bytes(...)],
            'strings_memory_used' => ['Strings memory used', $info['interned_strings_usage']['used_memory'], Formatter::bytes(...)],
            'strings_memory_free' => ['Strings memory free', $info['interned_strings_usage']['free_memory'], Formatter::bytes(...)],
            'number_of_strings' => ['Number of strings', $info['interned_strings_usage']['number_of_strings']],
        ];
    }

    /**
     * @param  array $stats
     * @return array
     */
    protected function getOpcacheStatsFields($stats)
    {
        return [
            'cached_scripts' => ['Cached scripts', $stats['num_cached_scripts']],
            'cached_keys' => ['Cached keys', $stats['num_cached_keys']],
            'max_cached_keys' => ['Max cached keys', $stats['max_cached_keys']],
            'start_time' => ['Start time', $stats['start_time'], fn ($time) => Formatter::date($time, 'U')],
            'last_restart_time' => ['Last restart time', $stats['last_restart_time'], fn ($time) => $time ? Formatter::date($time, 'U') : 'Never'],
            'oom_restarts' => ['Oom restarts', $stats['oom_restarts']],
            'hash_restarts' => ['Hash restarts', $stats['hash_restarts']],
            'manual_restarts' => ['Manual restarts', $stats['manual_restarts']],
            'hits' => ['Hits', $stats['hits']],
            'misses' => ['Misses', $stats['misses']],
            'blacklist_misses' => ['Blacklist misses', $stats['blacklist_misses']],
            'blacklist_miss_ratio' => ['Blacklist misses (%)', $stats['blacklist_miss_ratio'], fn ($percentage) => "{$percentage}%"],
            'opcache_hit_rate' => ['Opcache hit rate', $stats['opcache_hit_rate']],
        ];
    }
}
