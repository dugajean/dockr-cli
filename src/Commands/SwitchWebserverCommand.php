<?php

namespace Dicker\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SwitchWebserverCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'switch:webserver';

    const WEBSERVERS = [
        'apache', 'nginx'
    ];

    /**
     * Configure the command
     *
     * @return void
     */
    protected function configure()
    {
        $options = implode(',', self::WEBSERVERS);

        $this->setDescription('Switch the webserver powering this project.')
            ->setHelp("Switches the webserver of a project previously setup with Dicker. Allowed values are: {$options}");
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
