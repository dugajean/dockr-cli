<?php

declare(strict_types=1);

namespace Dockr;

use Dockr\EventSubscriber;
use Dockr\Events\ProjectPathHandler;
use Dockr\Events\EventHandlerInterface;
use Symfony\Component\Console\Application;
use Dockr\GlobalArguments\ProjectPathOption;
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
        array $commands
    )
    {
        $this->config = $config;
        $this->application = $app;
        $this->eventSubscriber = $eventSubscriber;
        $this->eventDispatcher = $eventDispatcher;
        $this->factoryCommandLoader = $factoryCommandLoader;
        $this->commandList = $commands;
    }

    /**
     * Attach event dispatcher
     *
     * @return void
     */
    private function attachEventsDispatcher(): void
    {
        $addListener = function (EventHandlerInterface $eventHandler): void {
            $this->eventDispatcher->addListener($eventHandler->onEvent(), $eventHandler->handler());
        };

        $addListener(new ProjectPathHandler($this->config));

        $this->application->setDispatcher($this->eventDispatcher);
    }

    /**
     * Register global arguments and options.
     *
     * @return void
     */
    private function registerGlobalArguments(): void
    {
        $definition = $this->application->getDefinition();

        $definition->addOption((new ProjectPathOption)->getOption());
    }

    /**
     * Assemble everything and run the app.
     *
     * @return void
     * @throws \Exception
     */
    public function run(): void
    {
        $this->attachEventsDispatcher();
        $this->registerGlobalArguments();
        $this->application->addCommands($this->commandList);
        $this->application->setCommandLoader($this->factoryCommandLoader);
        $this->eventSubscriber->listen();

        $this->application->run();
    }
}
