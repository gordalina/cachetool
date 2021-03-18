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
use Symfony\Component\Console\Output\OutputInterface;

class SelfUpdateCommand extends \SelfUpdate\SelfUpdateCommand
{
    protected function configure()
    {
        $app = $this->applicationName;

        $this
            ->setAliases(array('self-update'))
            ->setDescription("Updates $app to the latest version.")
            ->setHelp(
                <<<EOT
  The <info>self-update</info> command checks github for newer
  versions of $app and if found, installs the latest.
  EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $result = parent::execute($input, $output);

        if (is_int($result)) {
           return $result;
        }

        return 0;
    }
}
