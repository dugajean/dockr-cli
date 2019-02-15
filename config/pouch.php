<?php

use Pouch\Pouch;
use Dockr\Config;
use Humbug\SelfUpdate\Updater;
use Dockr\Commands\AliasCommand;
use Dockr\Events\EventSubscriber;
use Symfony\Component\Dotenv\Dotenv;
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
    Updater::class => pouch()->factory(function () {
        $file = file_exists('bin/dockr.phar') ? 'bin/dockr.phar' : null;

        $updater = new Updater($file, false);
        $updater->setStrategy(Updater::STRATEGY_GITHUB);
        $updater->getStrategy()->setPackageName('dugajean/dockr-cli');
        $updater->getStrategy()->setPharName('dockr.phar');

        return $updater;
    }),
    Dotenv::class => pouch()->factory(function () {
       return new Dotenv;
    }),
]);
