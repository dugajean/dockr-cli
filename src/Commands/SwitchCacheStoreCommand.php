<?php

namespace Dockr\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;

class SwitchCacheStoreCommand extends SwitchCommand
{
    /**
     * Configure the command
     *
     * @return void
     */
    protected function configure()
    {
        $options = comma_list(self::getChoices());

        $this
            ->setName('switch:cache')
            ->setDescription('Switch the cache store for your project')
            ->setHelp("Switches the cache store of a project previously setup with Dockr. Allowed values are: {$options}")
            ->setDefinition(
                new InputDefinition([
                    new InputArgument($this->getArgument(), InputArgument::REQUIRED, 'The new cache store you want to set'),
                ])
            );
    }

    /**
     * Return options
     *
     * @return array
     */
    public static function getChoices()
    {
        return ['redis', 'memcached'];
    }
}
