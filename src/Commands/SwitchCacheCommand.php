<?php

namespace Dockr\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SwitchCacheCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'switch:cache';

    const CACHE_STORES = [
        'redis', 'memcached'
    ];

    /**
     * Configure the command
     *
     * @return void
     */
    protected function configure()
    {
        $options = implode(',', self::CACHE_STORES);

        $this->setDescription('Switch the cache store for your project.')
            ->setHelp("Switches the cache store of a project previously setup with Dockr. Allowed values are: {$options}");
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // ...
    }
}
