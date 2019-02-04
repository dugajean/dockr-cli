<?php

namespace Dockr\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;

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
            ->setDefinition(
                new InputDefinition([
                    new InputArgument('new value', InputArgument::REQUIRED, 'The new PHP version you want to set'),
                ])
            );
    }

    /**
     * Add additional replacements/
     *
     * @param string $newValue
     *
     * @return void
     */
    protected function addReplacements($newValue)
    {
        $oldPhp = str_replace('.', '', $this->phpVersion);

        /**
         * Corresponds to the correct phpdockerio image
         * @see https://hub.docker.com/u/phpdockerio
         */
        $newPhp = $newValue == '7.0' ? '7' : str_replace('.', '', $newValue);

        $this->setReplacement($oldPhp, $newPhp);
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
