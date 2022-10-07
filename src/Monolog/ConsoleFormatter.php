<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CacheTool\Monolog;

use Monolog\Formatter\LineFormatter;
use Monolog\Level;
use Monolog\Logger;
use Monolog\LogRecord;

/**
 * Formats incoming records for console output by coloring them depending on log level.
 *
 * @author Tobias Schultze <http://tobion.de>
 */
class ConsoleFormatter extends LineFormatter
{
    const SIMPLE_FORMAT = "%extra.start_tag%[%datetime%] %channel%.%level_name%:%extra.end_tag% %message% %context% %extra%\n";

    /**
     * {@inheritdoc}
     */
    public function format(array|LogRecord $record): string
    {
        if (Logger::API < 3) {
            if ($record['level'] >= Logger::ERROR) {
                $record = $this->addTags($record, 'error');
            } elseif ($record['level'] >= Logger::NOTICE) {
                $record = $this->addTags($record, 'comment');
            } elseif ($record['level'] >= Logger::INFO) {
                $record = $this->addTags($record, 'info');
            } else {
                $record = $this->addTags($record);
            }

            return parent::format($record);
        }

        if (Level::Error->includes($record->level)) {
            $this->addTags($record, 'error');
        } elseif (Level::Notice->includes($record->level)) {
            $this->addTags($record, 'comment');
        } elseif (Level::Info->includes($record->level)) {
            $this->addTags($record, 'info');
        } else {
            $this->addTags($record);
        }

        return parent::format($record);
    }

    private function addTags(array|LogRecord $record, ?string $tag = null): array|LogRecord
    {
        $record['extra']['start_tag'] = $tag ? sprintf('<%s>', $tag) : '';
        $record['extra']['end_tag'] = $tag ? sprintf('</%s>', $tag) : '';

        return $record;
    }
}
