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
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Yaml;

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

    /**
     * @return $this
     */
    protected function addFormatOption()
    {
        return $this->addOption('format', null, InputOption::VALUE_REQUIRED, 'Output format: table, json, csv or yaml', 'table');
    }

    /**
     * @return $this
     */
    protected function addSortOption()
    {
        return $this->addOption('sort', null, InputOption::VALUE_REQUIRED, 'Sort the rows by a column. Prefix the column with "-" to sort in descending order. Example: --sort=-memory');
    }

    /**
     * Renders rows of raw values. Only the table format formats the values for humans.
     *
     * @param array $columns column key => [header, optional function that formats the value for the table]
     * @param array $rows list of arrays with the column keys
     */
    protected function renderRows(InputInterface $input, OutputInterface $output, array $columns, array $rows)
    {
        $sort = $input->hasOption('sort') ? $input->getOption('sort') : null;

        if ($sort) {
            $key = ltrim($sort, '-');

            if (!isset($columns[$key])) {
                throw new \InvalidArgumentException(sprintf('Unknown sort column "%s". Use one of: %s', $key, implode(', ', array_keys($columns))));
            }

            $order = str_starts_with($sort, '-') ? -1 : 1;
            usort($rows, fn ($a, $b) => $order * ($a[$key] <=> $b[$key]));
        }

        if ($input->getOption('format') !== 'table') {
            $this->export($input, $output, $rows, array_keys($columns), $rows);
            return;
        }

        $table = new Table($output);
        $table->setHeaders(array_column($columns, 0));

        foreach ($rows as $row) {
            $table->addRow(array_map(fn ($key, $column) => $this->display($column[1] ?? null, $row[$key]), array_keys($columns), $columns));
        }

        $table->render();
    }

    /**
     * Renders one record of raw values. The table format shows one field on each row.
     *
     * @param array $fields field key => [label, raw value, optional function that formats the value for the table]
     */
    protected function renderRecord(InputInterface $input, OutputInterface $output, array $fields)
    {
        $record = array_map(fn ($field) => $field[1], $fields);

        if ($input->getOption('format') !== 'table') {
            $this->export($input, $output, $record, array_keys($record), [$record]);
            return;
        }

        $table = new Table($output);
        $table->setHeaders(['Name', 'Value']);

        foreach ($fields as $field) {
            $table->addRow([$field[0], $this->display($field[2] ?? null, $field[1])]);
        }

        $table->render();
    }

    private function display(?callable $format, $value)
    {
        return $format ? $format($value) : $value;
    }

    private function export(InputInterface $input, OutputInterface $output, array $data, array $columns, array $rows)
    {
        $text = match ($format = $input->getOption('format')) {
            'json' => json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR) . "\n",
            'yaml' => rtrim(Yaml::dump($data, 4, 2, Yaml::DUMP_COMPACT_NESTED_MAPPING | Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE)) . "\n",
            'csv' => $this->csv($columns, $rows),
            default => throw new \InvalidArgumentException("Unknown format \"{$format}\". Use one of: table, json, csv, yaml"),
        };

        $output->write($text, false, OutputInterface::OUTPUT_RAW);
    }

    private function csv(array $columns, array $rows)
    {
        $stream = fopen('php://temp', 'r+');
        fputcsv($stream, $columns, escape: '');

        foreach ($rows as $row) {
            // a CSV cell holds one string or number, so other values are written as JSON
            fputcsv($stream, array_map(fn ($key) => is_scalar($row[$key]) && !is_bool($row[$key]) ? $row[$key] : json_encode($row[$key]), $columns), escape: '');
        }

        rewind($stream);

        return stream_get_contents($stream);
    }
}
