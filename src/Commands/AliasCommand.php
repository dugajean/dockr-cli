<?php

namespace Dockr\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class AliasCommand
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
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
        $this->commands = $commands;
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
     * @return array
     */
    public function getCommands()
    {
        return $this->commands;
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
             * @param \Symfony\Component\Console\Input\InputInterface   $input
             * @param \Symfony\Component\Console\Output\OutputInterface $output
             *
             * @return int|void|null
             */
            protected function execute(InputInterface $input, OutputInterface $output)
            {
                foreach ($this->alias->getCommands() as $command) {
                    $output->writeln(shell_exec($command));
                }
            }
        };
    }
}
