<?php

namespace Dockr\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExtensionDisableCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'ext:disable';

    /**
     * Configure the command
     *
     * @return void
     */
    protected function configure()
    {
        $this->setDescription('Disable one or more PHP modules')
            ->setHelp('Provide a comma separated list of PHP modules that you want to disable. Use --list to see all available options.');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
//        $this->setIO($input, $output);
    }
}
