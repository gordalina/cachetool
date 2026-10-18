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

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ApcuKeyFetchCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('apcu:key:fetch')
            ->setDescription('Shows the content of an APCu key')
            ->addArgument('key', InputArgument::REQUIRED)
            ->addFormatOption()
            ->setHelp('');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->ensureExtensionLoaded('apcu');

        $key = $input->getArgument('key');

        $success = new \stdClass();
        $value = $this->getCacheTool()->apcu_fetch($key, $success);

        if ($input->getOption('format') !== 'table') {
            $this->renderRecord($input, $output, [
                'key' => ['Key', $key],
                'exists' => ['Exists', $success->success],
                'value' => ['Value', $success->success ? $value : null],
            ]);
        } elseif ($success->success) {
            $output->writeln(sprintf("<comment>APCu key=<info>%s</info> has value=<info>%s</info></comment>", $key, var_export($value, true)));
        } else {
            $output->writeln("<comment>APCu key=<info>{$key}</info> does not exist.</comment>");
        }

        return $success->success ? 0 : 1;
    }
}
