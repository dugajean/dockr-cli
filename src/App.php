<?php

namespace Dockr;

use Dockr\Events\EventSubscriber;
use Symfony\Component\Console\Application;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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
     * @var EventDispatcherInterface
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
     * @var array
     */
    private $commandList;

    /**
     * App constructor.
     *
     * @param Config                    $config
     * @param Application               $app
     * @param EventSubscriber           $eventSubscriber
     * @param EventDispatcherInterface  $eventDispatcher
     * @param FactoryCommandLoader      $factoryCommandLoader
     * @param array                     $commandList
     */
    public function __construct(
        Config $config,
        Application $app,
        EventSubscriber $eventSubscriber,
        EventDispatcherInterface $eventDispatcher,
        FactoryCommandLoader $factoryCommandLoader,
        array $commandList
    )
    {
        $this->config = $config;
        $this->application = $app;
        $this->eventSubscriber = $eventSubscriber;
        $this->eventDispatcher = $eventDispatcher;
        $this->factoryCommandLoader = $factoryCommandLoader;
        $this->commandList = $commandList;
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
        $this->application->addCommands($this->commandList);
        $this->loadCommandsFromConfig();
        $this->attachEventsDispatcher();
        $this->eventSubscriber->listen();

        $this->application->run();
    }
}
