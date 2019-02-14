<?php

namespace Dockr\Commands;

use Dockr\Config;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class AliasCommand
{
    const TYPE_SHELL = 'shell';
    const TYPE_CLASS = 'class';

    /**
     * @var string
     */
    private $name;

    /**
     * @var \stdClass|Command
     */
    private $command;

    /**
     * @var string
     */
    private $type;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * AliasParser constructor.
     *
     * @param string       $name
     * @param array|string $command
     *
     * @throws \Pouch\Exceptions\NotFoundException
     * @throws \Pouch\Exceptions\PouchException
     */
    public function __construct($name, $command)
    {
        $this->name = $name;
        $this->output = pouch()->get(OutputInterface::class);
        $this->command = $this->prepareCommand($command);

        $this->populateEnvironment();
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
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * Returns an child-command instance.
     *
     * @return \stdClass|\Symfony\Component\Console\Command\Command
     */
    public function getClass()
    {
        return $this->type == self::TYPE_CLASS ? $this->getCommand() : new class ($this) extends Command
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
                foreach ($this->alias->getCommand() as $command) {
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
                foreach ($this->alias->getCommand() as $command) {
                    $commandStr = $command->body;
                    foreach ($command->arguments as $argument) {
                        $commandStr = str_replace("{{$argument}}", $input->getArgument($argument), $commandStr);
                    }

                    $output->write(process($commandStr, getenv()));
                }
            }
        };
    }

    /**
     * Prepares the command and their arguments.
     *
     * @param array|string $command
     *
     * @return mixed
     */
    private function prepareCommand($command)
    {
        if (is_array($command)) {
            $parsedCommand = array_map(function ($item) {
                $object = new \stdClass;
                $object->body = $item;
                preg_match_all('~\{([^}]*)\}~', $item, $matches);
                $object->arguments = $matches[1];
                return $object;
            }, $command);

            $this->type = self::TYPE_SHELL;
        } elseif (class_exists($command) && is_subclass_of($command, Command::class)) {
            $parsedCommand = new $command($this->getName());
            $this->type = self::TYPE_CLASS;
        } else {
            $this->output->writeln(
                color('red', "Invalid dockr.json command alias detected. Please check '{$this->getName()}' and try again.", true)
            );

            exit(1);
        }

        return $parsedCommand;
    }

    /**
     * Populate the environment.
     *
     * @return void
     * @throws \Pouch\Exceptions\NotFoundException
     * @throws \Pouch\Exceptions\PouchException
     */
    private function populateEnvironment()
    {
        $config = pouch()->get(Config::class);
        $dotEnv = pouch()->get(Dotenv::class);

        $envData = [];
        foreach (Config::STRUCTURE as $configKey) {
            if ($val = $config->get($configKey)) {
                $key = strtoupper(str_replace('-', '_', $configKey));
                $val = is_array($val) ? implode(' ', array_flatten($val)) : $val;
                $envData[$key] = $val;
            }
        }

        $envData['PHP_VERSION_X10']  = $envData['PHP_VERSION'] == '7.0' ? '7' : $envData['PHP_VERSION'] * 10;
        $envData['WEB_SERVER_VHOST'] = SwitchWebServerCommand::getConf($envData['WEB_SERVER']);
        $dotEnv->populate($envData, true);

        $envFile = $config->get('environment-file') ?? '.env';
        if ($envFile && file_exists(current_path($envFile))) {
            $dotEnv->load($envFile);
        }
    }
}
