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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PhpEvalCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('php:eval')
            ->setDescription('Run arbitrary PHP code from an argument or file')
            ->addOption('run', 'r', InputOption::VALUE_REQUIRED)
            ->addOption('file', 'f', InputOption::VALUE_REQUIRED)
            ->addOption('format', null, InputOption::VALUE_REQUIRED, '', 'string')
            ->setHelp("In order to display output, ensure the code returns something.\nExample: <info>cachetool php:eval -r 'return gethostname();'</info>");
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->getOption('run')) {
            $code = $input->getOption('run');
        } else if ($input->getOption('file')) {
            $path = $input->getOption('file');

            if (!is_file($path)) {
              throw new \RuntimeException("Could not read file: {$path}");
            }

            $code = file_get_contents($path);
            $code = str_replace('<?php','',$code);
        } else {
          throw new \RuntimeException("Need to specify a --run or a --file input option");
        }

        $result = $this->getCacheTool()->_eval($code);

        switch ($input->getOption('format')) {
          case 'var_export': $stringified = var_export($result, true); break;
          case 'json': $stringified = json_encode($result); break;
          case 'string': $stringified = sprintf('%s', $result); break;
        }

        $output->writeln($stringified);
        return 0;
    }
}
