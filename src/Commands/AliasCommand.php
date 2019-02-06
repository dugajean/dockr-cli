<?php

namespace Dockr\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class AliasCommand
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var \stdClass
     */
    private $commands;

    /**
     * AliasParser constructor.
     *
     * @param string $name
     * @param array  $commands
     */
    public function __construct($name, array $commands)
    {
        $this->name = $name;
        $this->commands = $this->prepareCommands($commands);
    }

    /**
     * Name getter.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Commands getter.
     *
     * @return \stdClass
     */
    public function getCommands()
    {
        return $this->commands;
    }

    /**
     * Prepares the commands and their arguments.
     *
     * @param array $commands
     *
     * @return array
     */
    private function prepareCommands(array $commands)
    {
        return array_map(function ($item) {
            $object = new \stdClass;
            $object->body = $item;
            preg_match_all('~\{([^}]*)\}~', $item, $matches);
            $object->arguments = $matches[1];
            return $object;
        }, $commands);
    }

    /**
     * Returns an child-command instance.
     *
     * @return \Symfony\Component\Console\Command\Command
     */
    public function getClass()
    {
        return new class ($this) extends Command
        {
            /**
             * @var \Dockr\Commands\AliasCommand
             */
            private $alias;

            /**
             * Anonymous class constructor.
             *
             * @param \Dockr\Commands\AliasCommand $alias
             *
             * @return void
             */
            public function __construct(AliasCommand $alias)
            {
                $this->alias = $alias;
                parent::__construct($this->alias->getName());
            }

            /**
             * @inheritdoc
             */
            protected function configure()
            {
                foreach ($this->alias->getCommands() as $command) {
                    foreach ($command->arguments as $argument) {
                        $this->addArgument($argument, InputArgument::REQUIRED);
                    }
                }
            }

            /**
             * @inheritdoc
             */
            protected function execute(InputInterface $input, OutputInterface $output)
            {
                foreach ($this->alias->getCommands() as $command) {
                    $commandStr = $command->body;
                    foreach ($command->arguments as $argument) {
                        $commandStr = str_replace("{{$argument}}", $input->getArgument($argument), $commandStr);
                    }

                    $output->writeln(shell_exec($commandStr));
                }
            }
        };
    }
}
