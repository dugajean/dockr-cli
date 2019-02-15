<?php

namespace Dockr\Commands;

use function Dockr\Helpers\comma_list;
use Symfony\Component\Console\Input\InputArgument;

class SwitchPhpVersionCommand extends SwitchCommand
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
            ->setName('switch:php')
            ->setDescription('Switch the PHP version of this project')
            ->setHelp("Switches the PHP version of a project previously setup with Dockr. Allowed values are: {$options}")
            ->addArgument($this->getArgument(), InputArgument::REQUIRED, 'The new PHP version you want to set');
    }

    /**
     * Return options
     *
     * @return array
     */
    public static function getChoices()
    {
        return ['7.0', '7.1', '7.2', '7.3'];
    }
}
