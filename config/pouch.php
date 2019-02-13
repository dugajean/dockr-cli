<?php

use Humbug\SelfUpdate\Updater;
use Pouch\Pouch;
use Dockr\Config;
use Dockr\Events\EventSubscriber;
use Dockr\Commands\AliasCommand;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Console\CommandLoader\FactoryCommandLoader;

$rootPath = __DIR__ . '/../';

Pouch::bootstrap($rootPath);

pouch()->bind([
    'stubs_finder' => function () use ($rootPath) {
        return (new Finder())->in($rootPath . 'stubs')->name('*.stub')->ignoreDotFiles(false);
    },
    'docker_finder' => pouch()->factory(function () use ($rootPath) {
        $namePatterns = ['docker-compose.yml', 'default.conf', 'Dockerfile'];

        try {
            $finder = (new Finder())->in('./')->files()->name($namePatterns)->ignoreDotFiles(false);
        } catch (\InvalidArgumentException $e) {
            throw new \RuntimeException("Dockr has not been initialized in this path. Please run 'dockr init' first");
        }

        return $finder;
    }),
    OutputInterface::class => pouch()->factory(function () {
        return new ConsoleOutput;
    }),
    Config::class => function () {
        return new Config;
    },
    Application::class => function () {
        return new Application('Dockr CLI', '@package_version@');
    },
    EventDispatcher::class => function () {
        return new EventDispatcher;
    },
    AliasCommand::class => function ($pouch) {
        $commandInstances = [];
        $config = $pouch->get(Config::class);
        $commands = $config->get('alias-commands');

        foreach ((array)$commands as $alias => $commandList) {
            $commandInstances[$alias] = function () use ($alias, $commandList) {
                return (new AliasCommand($alias, $commandList))->getClass();
            };
        }

        return $commandInstances;
    },
    FactoryCommandLoader::class => function ($pouch) {
        return new FactoryCommandLoader($pouch->get(AliasCommand::class));
    },
    EventSubscriber::class => function ($pouch) {
        return new EventSubscriber($pouch->get(Config::class), $pouch->get(EventDispatcher::class));
    },
    Updater::class => function () {
        $updater = new Updater('bin/dockr.phar', false);
        $updater->setStrategy(Updater::STRATEGY_GITHUB);
        $updater->getStrategy()->setPackageName('dugajean/dockr-cli');
        $updater->getStrategy()->setPharName('dockr.phar');

        return $updater;
    }
]);
