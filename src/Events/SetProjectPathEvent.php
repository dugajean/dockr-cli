<?php

namespace Dockr\Events;

use Dockr\Config;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SetProjectPathEvent
{
    /**
     * @var EventDispatcher
     */
    protected $dispatcher;

    /**
     * @var Config
     */
    protected $config;

    /**
     * SetProjectPathEvent constructor.
     */
    public function __construct(EventDispatcherInterface $dispatcher, Config $config)
    {
        $this->dispatcher = $dispatcher;
        $this->config = $config;
    }

    /**
     * Handles this event
     *
     * @return void
     */
    public function register()
    {
        $this->dispatcher->addListener(ConsoleEvents::COMMAND, function (ConsoleCommandEvent $event) {

            $input = $event->getInput();

            if ($input->hasOption('project-path') === true) {
                $this->config->setConfigFile($input->getOption('project-path'));
            }
        });
    }
}