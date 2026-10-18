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

class ApcuCacheInfoCommand extends AbstractCommand
{
    protected static $apcFix = [
        'stime' => "start_time",
        'atime' => "access_time",
        'mtime' => "modification_time",
        'ctime' => "creation_time",
        'dtime' => "deletion_time",

        'nslots' => "num_slots",
        'nhits' => "num_hits",
        'nmisses' => "num_misses",
        'ninserts' => "num_inserts",
        'nentries' => "num_entries",
        'nexpunges' => "expunges",
        "num_expunges" => "expunges",

        'key' => "info",
    ];

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('apcu:cache:info')
            ->setDescription('Shows APCu user & system cache information')
            ->addFormatOption()
            ->setHelp('');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->ensureExtensionLoaded('apcu');

        $info = $this->getCacheTool()->apcu_cache_info(true);

        $this->normalize($info);

        if (empty($info)) {
            throw new \RuntimeException("Could not fetch info from APCu");
        }

        $this->renderRecord($input, $output, $this->getFields($info));

        return 0;
    }

    /**
     * @param  array $info
     * @return array
     */
    protected function getFields($info)
    {
        // missing: cache_list, deleted_list, slot_distribution
        return [
            'slots' => ['Slots', $info['num_slots']],
            'ttl' => ['TTL', $info['ttl']],
            'hits' => ['Hits', $info['num_hits'], number_format(...)],
            'misses' => ['Misses', $info['num_misses'], number_format(...)],
            'inserts' => ['Inserts', $info['num_inserts'], number_format(...)],
            'expunges' => ['Expunges', $info['expunges'], number_format(...)],
            'start_time' => ['Start time', $info['start_time'], fn ($time) => Formatter::date($time, 'U')],
            'memory_size' => ['Memory size', $info['mem_size'], Formatter::bytes(...)],
            'entries' => ['Entries', $info['num_entries'], number_format(...)],
            'file_upload_progress' => ['File upload progress', (bool) ini_get('apcu.rfc1867'), Formatter::bool(...)],
            'memory_type' => ['Memory type', $info['memory_type']],
            'locking_type' => ['Locking type', $info['locking_type'] ?? null, fn ($type) => $type ?? 'Not Supported'],
        ];
    }

    /**
     * Fix inconsistencies between APC and APCu
     *
     * @param  array  &$array
     */
    protected function normalize(array &$array)
    {
        foreach ($array as $key => $value) {
            if (array_key_exists($key, self::$apcFix)) {
                unset($array[$key]);
                $array[self::$apcFix[$key]] = $value;
            }
        }
    }
}
