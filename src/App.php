<?php

namespace Dockr;

use Dockr\Commands;
use Dockr\Events\EventSubscriber;
use Symfony\Component\Console\Application;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Console\CommandLoader\FactoryCommandLoader;

final class App
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Application
     */
    private $application;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var EventSubscriber
     */
    private $eventSubscriber;

    /**
     * @var FactoryCommandLoader
     */
    private $factoryCommandLoader;

    /**
     * App constructor.
     *
     * @return void
     * @throws \Pouch\Exceptions\NotFoundException
     * @throws \Pouch\Exceptions\PouchException
     */
    public function __construct()
    {
        $this->config = pouch()->get(Config::class);
        $this->application = pouch()->get(Application::class);
        $this->eventSubscriber = pouch()->get(EventSubscriber::class);
        $this->eventDispatcher = pouch()->get(EventDispatcher::class);
        $this->factoryCommandLoader = pouch()->get(FactoryCommandLoader::class);
    }

    /**
     * Register the main commands
     *
     * @return array
     */
    private function commands()
    {
        return [
            new Commands\InitCommand,
            new Commands\UpdateCommand,
            new Commands\SwitchWebServerCommand,
            new Commands\SwitchPhpVersionCommand,
            new Commands\SwitchCacheStoreCommand,
        ];
    }

    /**
     * Register commands declared in config.
     *
     * @return void
     */
    private function loadCommandsFromConfig()
    {
        $this->application->setCommandLoader($this->factoryCommandLoader);
    }

    /**
     * Attach event dispatcher
     *
     * @return void
     */
    private function attachEventsDispatcher()
    {
        $this->application->setDispatcher($this->eventDispatcher);
    }

    /**
     * Assemble everything and run the app.
     *
     * @return void
     * @throws \Exception
     */
    public function run()
    {
        $this->application->addCommands($this->commands());
        $this->loadCommandsFromConfig();
        $this->attachEventsDispatcher();
        $this->eventSubscriber->listen();

        $this->application->run();
    }
}
