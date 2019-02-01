<?php

namespace Dicker\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SwitchPhpCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'switch:php';

    const PHP_VERSIONS = [
        '7.0', '7.1', '7.2', '7.3'
    ];

    /**
     * Configure the command
     *
     * @return void
     */
    protected function configure()
    {
        $options = implode(',', self::PHP_VERSIONS);

        $this->setDescription('Switch the PHP version of this project.')
            ->setHelp("Switches the PHP version of a project previously setup with Dicker. Allowed values are: {$options}");
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
