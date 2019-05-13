<?php

declare(strict_types=1);

namespace Dockr;

use Dockr\Config;
use function Dockr\Helpers\{color, process};
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;

class EventSubscriber
{
    /**
     * Accepted types
     */
    const TYPE_PRE  = 'pre';
    const TYPE_ERR  = 'error';
    const TYPE_POST = 'post';

    /**
     * Map dockr events to the symfony ones.
     */
    const EVENT_MAP = [
        self::TYPE_PRE  => ConsoleEvents::COMMAND,
        self::TYPE_ERR  => ConsoleEvents::ERROR,
        self::TYPE_POST => ConsoleEvents::TERMINATE,
    ];

    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $scripts;

    /**
     * @var array
     */
    protected $commandList;

    /**
     * @var EventDispatcher
     */
    protected $dispatcher;

    /**
     * EventSubscriber constructor.
     *
     * @param \Dockr\Config   $config
     * @param EventDispatcher $dispatcher
     *
     * @return void
     */
    public function __construct(Config $config, EventDispatcher $dispatcher)
    {
        $this->scripts = $config->get('scripts') ?? [];
        $this->dispatcher = $dispatcher;
    }

    /**
     * Return all the commands and methods.
     *
     * @param bool $continueOnError
     *
     * @return void
     */
    public function listen(bool $continueOnError = false): void
    {
        foreach (self::EVENT_MAP as $event => $eventName) {
            $this->dispatcher->addListener(self::EVENT_MAP[$event], $this->eventHandler($event, $continueOnError));
        }
    }

    /**
     * Event handler.
     *
     * @param string $event
     * @param bool   $continueOnError
     *
     * @return \Closure
     */
    protected function eventHandler(string $event, bool $continueOnError): \Closure
    {
        return function (ConsoleEvent $e) use ($event, $continueOnError) {
            if (
                (!$continueOnError && $e instanceof ConsoleTerminateEvent && $e->getExitcode() !== 0)
                || $e->getCommand() === null
            ) {
                return;
            }

            $commandName = str_replace(':', '-', $e->getCommand()->getName());

            if (!$this->eventExists($event, $commandName)) {
                return;
            }

            foreach ((array)$this->scripts[$event . '-' . $commandName] as $command) {
                if (strpos($command, '::') !== false) {
                    list($class, $method) = explode('::', $command);
                    if (class_exists($class) && method_exists($class, $method)) {
                        try {
                            $methodOutput = $class::{$method}($e->getInput(), $e->getOutput(), $e->getCommand());
                        } catch (\Error $ex) {
                            $e->getOutput()->writeln(
                                color('red', "Couldn't execute method '{$command}'. Ensure that the visibility is set to public.", true)
                            );

                            exit(255);
                        }

                        $e->getOutput()->writeln($methodOutput);
                    }
                } else {
                    $e->getOutput()->writeln(process($command));
                }
            }
        };
    }

    /**
     * Checks if hook has been registered.
     *
     * @param string $event
     * @param string $command
     *
     * @return bool
     */
    protected function eventExists(string $event, string $command): bool
    {
        return array_key_exists($event . '-' . $command, $this->scripts);
    }
}
