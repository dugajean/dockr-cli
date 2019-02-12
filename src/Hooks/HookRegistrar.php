<?php

namespace Dockr\Hooks;

use Dockr\Config;
use Dockr\Commands\Command;

class HookRegistrar
{
    /**
     * Accepted types
     */
    const TYPE_PRE  = 'pre';
    const TYPE_POST = 'post';

    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $hooks;

    /**
     * @var Command
     */
    protected $command;

    /**
     * @var array
     */
    protected $commandList;

    /**
     * HookRegistrar constructor.
     *
     * @param \Dockr\Config $config
     *
     * @return void
     */
    public function __construct(Config $config)
    {
        $this->hooks = $config->get('scripts') ?? [];
    }

    /**
     * Return all the commands and methods.
     *
     * @return $this
     */
    protected function run()
    {
        foreach ((array)$this->commandList as $command) {
            if (strpos($command, '::') !== false) {
                list($class, $method) = explode('::', $command);

                if (class_exists($class) && method_exists($class, $method)) {
                    $this->command->getOutput()->writeln($class::{$method}($this->command));
                }
            } else {
                $this->command->getOutput()->writeln(shell_exec($command));
            }
        }

        return $this;
    }

    /**
     * Register the proper hooks (pre or post).
     *
     * @param string $type
     *
     * @return $this
     */
    public function register($type)
    {
        $this->setType($type);

        $commandName  = str_replace(':', '-', $this->command->getName());

        foreach ($this->hooks as $hook => $commandList) {
            if (starts_with($hook, $this->type) && strpos($hook, $commandName) !== false) {
                $this->commandList = $commandList;
            }
        }

        return $this->run();
    }

    /**
     * Set the type.
     *
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        if ($type != self::TYPE_PRE && $type != self::TYPE_POST) {
            throw new \RuntimeException('Invalid hook type requested.');
        }

        $this->type = $type;

        return $this;
    }

    /**
     * Set the command.
     *
     * @param Command $command
     *
     * @return $this
     */
    public function setCommand(Command $command)
    {
        $this->command = $command;

        return $this;
    }
}
