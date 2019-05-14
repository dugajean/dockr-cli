<?php

declare(strict_types=1);

namespace Dockr\Events;

interface EventHandlerInterface
{
    /**
     * Return the event name.
     * 
     * Options: 
     *  - ConsoleEvents::COMMAND
     *  - ConsoleEvents::ERROR
     *  - ConsoleEvents::TERMINATE
     *
     * @return string
     */
    public function onEvent(): string;

    /**
     * Return the logic to handle the event.
     *
     * @return \Closure
     */
    public function handle(): \Closure;
}
