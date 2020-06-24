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

use CacheTool\Util\ManifestUpdateStrategy;
use Humbug\SelfUpdate\Updater;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SelfUpdateCommand extends Command
{
    const MANIFEST_FILE = 'https://gordalina.github.io/cachetool/manifest.json';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('self-update')
            ->setAliases(['selfupdate'])
            ->setDescription('Updates cachetool.phar to the latest version')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $updater = new Updater(null, false);
        $updater->setStrategyObject(new ManifestUpdateStrategy());
        $updater->getStrategy()->setManifestUrl(self::MANIFEST_FILE);
        
        if (!$updater->hasUpdate()) {
            $output->writeln(sprintf('You are already using the latest version: <info>%s</info>', $this->getApplication()->getVersion()));
            return 0;
        }

        $output->writeln(sprintf('Updating to SHA-1 <info>%s</info>', $updater->getNewVersion()));

        if(!$updater->update()) {
            $output->writeln(sprintf('Error during update: You are still using SHA-1 <info>%s</info>', $updater->getOldVersion()));
            return 1;
        }

        return 0;
    }
}
