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
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OpcacheStatusCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('opcache:status')
            ->setDescription('Show summary information about the opcode cache')
            ->setHelp('');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->ensureExtensionLoaded('Zend OPcache');

        $info = $this->getCacheTool()->opcache_get_status(false);

        if ($info === false) {
            throw new \RuntimeException('opcache_get_status(): No Opcache status info available.  Perhaps Opcache is disabled via opcache.enable or opcache.enable_cli?');
        }

        $table = new Table($output);
        $table->setHeaders(['Name', 'Value']);
        $table->setRows($this->getRows($info, $info['opcache_statistics']));
        $table->render();
    }

    /**
     * @param  array $info
     * @param  array $stats
     * @return array
     */
    protected function getRows($info, $stats)
    {
        $rows = $this->getGeneralRows($info);

        if (isset($info['interned_strings_usage'])) {
            $rows = array_merge($rows, $this->getStringsRows($info));
        }

        return array_merge($rows, $this->getOpcacheStatsRows($stats));
    }

    /**
     * @param  array $info
     * @return array
     */
    protected function getGeneralRows($info)
    {
        return [
            ['Enabled', $info['opcache_enabled'] ? 'Yes' : 'No'],
            ['Cache full', $info['cache_full'] ? 'Yes' : 'No'],
            ['Restart pending', $info['restart_pending'] ? 'Yes' : 'No'],
            ['Restart in progress', $info['restart_in_progress'] ? 'Yes' : 'No'],
            ['Memory used', Formatter::bytes($info['memory_usage']['used_memory'])],
            ['Memory free', Formatter::bytes($info['memory_usage']['free_memory'])],
            ['Memory wasted (%)', sprintf("%s (%s%%)", Formatter::bytes($info['memory_usage']['wasted_memory']), $info['memory_usage']['current_wasted_percentage'])],
        ];
    }

    /**
     * @param  array $info
     * @return array
     */
    protected function getStringsRows($info)
    {
        return [
            ['Strings buffer size', Formatter::bytes($info['interned_strings_usage']['buffer_size'])],
            ['Strings memory used', Formatter::bytes($info['interned_strings_usage']['used_memory'])],
            ['Strings memory free', Formatter::bytes($info['interned_strings_usage']['free_memory'])],
            ['Number of strings', $info['interned_strings_usage']['number_of_strings']],
        ];
    }

    /**
     * @param  array $stats
     * @return array
     */
    protected function getOpcacheStatsRows($stats)
    {
        return [
            new TableSeparator(),
            ['Cached scripts', $stats['num_cached_scripts']],
            ['Cached keys', $stats['num_cached_keys']],
            ['Max cached keys', $stats['max_cached_keys']],
            ['Start time', Formatter::date($stats['start_time'], 'U')],
            ['Last restart time', $stats['last_restart_time'] ? Formatter::date($stats['last_restart_time'], 'U') : 'Never'],
            ['Oom restarts', $stats['oom_restarts']],
            ['Hash restarts', $stats['hash_restarts']],
            ['Manual restarts', $stats['manual_restarts']],
            ['Hits', $stats['hits']],
            ['Misses', $stats['misses']],
            ['Blacklist misses (%)', sprintf('%s (%s%%)', $stats['blacklist_misses'], $stats['blacklist_miss_ratio'])],
            ['Opcache hit rate', $stats['opcache_hit_rate']],
        ];
    }
}
