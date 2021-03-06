<?php

declare(strict_types=1);

namespace Dockr\Commands;

use Dockr\Config;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function Dockr\Helpers\{
    env,
    color, 
    process, 
    is_assoc,
    add_slash, 
    current_path, 
    array_flatten
};

final class AliasCommand
{
    /**
     * Alias command types
     */
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
    private $commandType;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Dotenv
     */
    private $dotenv;

    /**
     * A list of replacements that are reserved and wont be parsed as args.
     *
     * @var array
     */
    private $reserved = ['DOCKR_PATH'];

    /**
     * AliasParser constructor.
     *
     * @param string          $name
     * @param array|string    $command
     * @param OutputInterface $output
     * @param Config          $config
     * @param Dotenv          $dotenv
     */
    public function __construct(
        string $name, 
        $command, 
        OutputInterface $output, 
        Config $config, 
        Dotenv $dotenv
    ) {
        $this->name = $name;
        $this->output = $output;
        $this->command = $this->prepareCommand($command);
        $this->config = $config;
        $this->dotenv = $dotenv;

        $this->populateEnvironment();
    }

    /**
     * Name getter.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Commands getter.
     *
     * @return \stdClass|\Symfony\Component\Console\Command\Command
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
        return $this->commandType == self::TYPE_CLASS 
            ? $this->getCommand() 
            : $this->anonymousCommand();
    }

    /**
     * Fetch anonymous class to handle alias.
     *
     * @return \Symfony\Component\Console\Command\Command
     */
    private function anonymousCommand(): Command
    {
        return new class($this) extends Command
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
            protected function configure(): void
            {
                $commandList = $this->alias->getCommand();

                foreach ($commandList as $command) {
                    $this->setDescription($command->help);
                    foreach ($command->args as $argument) {
                        $this->addArgument($argument, InputArgument::REQUIRED);
                    }
                }
            }

            /**
             * @inheritdoc
             */
            protected function execute(InputInterface $input, OutputInterface $output): void
            {
                foreach ($this->alias->getCommand() as $command) {
                    $commandStr = $command->body;
                    foreach ($command->args as $argument) {
                        $commandStr = str_replace("{{$argument}}", $input->getArgument($argument), $commandStr);
                    }

                    $output->writeln(color('yellow', "> $commandStr"));
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
            $isAssoc = is_assoc($command);
            $cmdList = $isAssoc ? $command['commands'] : $command;
            $helpTxt = $isAssoc ? $command['help'] : 'Alias command';

            $parsedCommand = array_map(function ($item) use ($helpTxt) {
                $object = new \stdClass;

                $object->args = $this->parseArguments($item);
                $object->help = $helpTxt;
                $object->body = $item;

                return $object;
            }, $cmdList);

            $this->commandType = self::TYPE_SHELL;
        } elseif (class_exists($command) && is_subclass_of($command, Command::class)) {
            $parsedCommand = new $command($this->getName());
            $this->commandType = self::TYPE_CLASS;
        } else {
            $this->output->writeln(
                color('red', "Invalid dockr.json command alias detected. Please check '{$this->getName()}' and try again.", true)
            );

            exit(1);
        }

        return $parsedCommand;
    }

    /**
     * Parses the arguments in the commands string and also
     * checks for reserved keys within the string and replaces them
     * with their respective environment variable in the string directly.
     *
     * @param string $command
     *
     * @return array
     */
    private function parseArguments(string &$command): array
    {
        preg_match_all('~\{([^} ]*)\}~', $command, $matches);
        
        $parsedArguments = $matches[1];
        foreach ($parsedArguments as $key => $arg) {
            if (in_array($arg, $this->reserved)) {
                $command = str_replace("{{$arg}}", current_path(), $command);
                unset($parsedArguments[$key]);
            }
        }

        return $parsedArguments;
    }

    /**
     * Populate the environment.
     *
     * @return void
     */
    private function populateEnvironment(): void
    {
        $this->mainEnv();
        $this->fileEnv();
    }

    /**
     * Loads dockr.json main information into the env.
     *
     * @return void
     */
    private function mainEnv(): void
    {
        foreach (Config::STRUCTURE as $configKey) {
            if ($val = $this->config->get($configKey)) {
                $key = strtoupper(str_replace('-', '_', $configKey));
                $val = is_array($val) ? implode(' ', array_flatten($val)) : $val;
                $envData[$key] = $val;
            }
        }

        $envData['HOST_HOSTNAME'] = 'host.docker.internal';
        $envData['WEB_SERVER_VHOST'] = SwitchWebServerCommand::getConf($envData['WEB_SERVER']);
        $envData['PHP_VERSION_X10'] = $envData['PHP_VERSION'] == '7.0' ? '7' : $envData['PHP_VERSION'] * 10;

        $dockerRoot = '/app';
        $dockerRoot .= array_key_exists('PUBLIC_PATH', $envData) ? add_slash($envData['PUBLIC_PATH']) : '/app/public';
        $envData['PUBLIC_PATH'] = $dockerRoot;

        $this->dotenv->populate($envData, true);
    }

    /**
     * Loads .env into the env.
     *
     * @return void
     */
    private function fileEnv(): void
    {
        $envFile = $this->config->get('environment-file') ?? '.env';
        
        if ($envFile && file_exists(current_path($envFile))) {
            $this->dotenv->load($envFile);
        }
    }
}
