<?php

namespace CacheTool\Command;

use CacheTool\Code;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCommand extends Command
{
    protected function getCacheTool()
    {
        return $this->getApplication()->getCacheTool();
    }

    protected function ensureExtensionLoaded($extension)
    {
        if (!$this->getCacheTool()->extension_loaded($extension)) {
            throw new \Exception("Extension `{$extension}` is not loaded");
        }
    }
}
