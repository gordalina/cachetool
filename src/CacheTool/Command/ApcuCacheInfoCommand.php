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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ApcuCacheInfoCommand extends AbstractCommand
{
    protected static $apcFix = array(
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
    );

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('apcu:cache:info')
            ->setDescription('Shows APCu user & system cache information')
            ->setHelp('');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->ensureExtensionLoaded('apcu');

        $info = $this->getCacheTool()->apcu_cache_info(true);

        $this->normalize($info);

        if (empty($info)) {
            throw new \RuntimeException("Could not fetch info from APCu");
        }

        $table = new Table($output);
        $table->setHeaders(array('Name', 'Info'));
        $table->setRows($this->getRows($info));
        $table->render($output);
    }

    /**
     * @param  array $info
     * @return array
     */
    protected function getRows($info)
    {
        // missing: cache_list, deleted_list, slot_distribution
        return array(
            array('Slots', $info['num_slots']),
            array('TTL', $info['ttl']),
            array('Hits', number_format($info['num_hits'])),
            array('Misses', number_format($info['num_misses'])),
            array('Inserts', number_format($info['num_inserts'])),
            array('Expunges', number_format($info['expunges'])),
            array('Start time', Formatter::date($info['start_time'], 'U')),
            array('Memory size', Formatter::bytes($info['mem_size'])),
            array('Entries', number_format($info['num_entries'])),
            array('File upload progress', ini_get('apcu.rfc1867') ? 'Yes' : 'No'),
            array('Memory type', $info['memory_type']),
            array('Locking type', (isset($info['locking_type']) ? $info['locking_type'] : 'Not Supported')),
        );
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
