<?php

namespace Dockr\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SwitchCacheStoreCommand extends Command
{
    /**
     * Configure the command
     *
     * @return void
     */
    protected function configure()
    {
        $options = implode(', ', self::getOptions());

        $this
            ->setName('switch:cache')
            ->setDescription('Switch the cache store for your project.')
            ->setHelp("Switches the cache store of a project previously setup with Dockr. Allowed values are: {$options}")
            ->setDefinition(
                new InputDefinition([
                    new InputArgument('new value', InputArgument::REQUIRED, 'The new cache store you want to set'),
                ])
            );
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int|void|null
     * @throws \Pouch\Exceptions\NotFoundException
     * @throws \Pouch\Exceptions\PouchException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        parent::basicReplacement();
    }

    /**
     * Return options
     *
     * @return array
     */
    public static function getOptions()
    {
        return ['redis', 'memcached'];
    }
}
