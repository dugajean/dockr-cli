<?php

namespace Dockr\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SwitchPhpVersionCommand extends Command
{
    /**
     * Configure the command
     *
     * @return void
     */
    protected function configure()
    {
        $options = implode(',', self::getOptions());

        $this
            ->setName('switch:php')
            ->setDescription('Switch the PHP version of this project.')
            ->setHelp("Switches the PHP version of a project previously setup with Dockr. Allowed values are: {$options}")
            ->setDefinition(
                new InputDefinition([
                    new InputArgument('new value', InputArgument::REQUIRED, 'The new PHP version you want to set'),
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
        parent::basicReplacement(function ($newValue) {
            $oldPhp = str_replace('.', '', $this->phpVersion);
            $newPhp = str_replace('.', '', $newValue);
            $this->replacements[$oldPhp] = $newPhp;
        });
    }

    /**
     * Return options
     *
     * @return array
     */
    public static function getOptions()
    {
        return ['7.0', '7.1', '7.2', '7.3'];
    }
}
