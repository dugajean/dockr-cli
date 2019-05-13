<?php

use Dockr\App;
use Pouch\Pouch;
use Dockr\Config;
use Dockr\Commands;
use Dockr\EventSubscriber;
use Humbug\SelfUpdate\Updater;
use Dockr\Commands\AliasCommand;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Finder\Finder;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\CommandLoader\FactoryCommandLoader;

$rootPath = __DIR__ . '/../';

Pouch::bootstrap($rootPath);

pouch()->bind([
    'StubsFinder' => function () use ($rootPath) {
        return (new Finder())->in($rootPath . 'stubs')->name('*.stub')->ignoreDotFiles(false);
    },
    OutputInterface::class => function () {
        return new ConsoleOutput;
    },
    Config::class => function () {
        return new Config;
    },
    Application::class => function () {
        return new Application('Dockr CLI', '@package_version@');
    },
    EventDispatcherInterface::class => function () {
        return new EventDispatcher;
    },
    AliasCommand::class => function (ContainerInterface $pouch) {
        $commandInstances = [];
        $config = $pouch->get(Config::class);
        $commands = $config->get('aliases');

        foreach ((array)$commands as $alias => $commandList) {
            $commandInstances[$alias] = function () use ($alias, $commandList, $pouch) {
                $aliasCommand = new AliasCommand(
                    $alias,
                    $commandList,
                    $pouch->get(OutputInterface::class),
                    $pouch->get(Config::class),
                    new Dotenv
                );

                return $aliasCommand->getClass();
            };
        }

        return $commandInstances;
    },
    FactoryCommandLoader::class => function (ContainerInterface $pouch) {
        return new FactoryCommandLoader($pouch->get(AliasCommand::class));
    },
    EventSubscriber::class => function (ContainerInterface $pouch) {
        return new EventSubscriber($pouch->get(Config::class), $pouch->get(EventDispatcherInterface::class));
    },
    Updater::class => function () {
        $file = file_exists('bin/dockr.phar') ? 'bin/dockr.phar' : null;

        $updater = new Updater($file, false);
        $updater->setStrategy(Updater::STRATEGY_GITHUB);
        $updater->getStrategy()->setPackageName('dugajean/dockr-cli');
        $updater->getStrategy()->setPharName('dockr.phar');

        return $updater;
    },
    'CommandList' => function (ContainerInterface $pouch) {
        return [
            new Commands\InitCommand($pouch->get('StubsFinder')),
            new Commands\UpdateCommand,
            new Commands\SwitchWebServerCommand,
            new Commands\SwitchPhpVersionCommand,
            new Commands\SwitchCacheStoreCommand,
        ];
    },
    App::class => function (ContainerInterface $pouch) {
        return new App(
            $pouch->get(Config::class),
            $pouch->get(Application::class),
            $pouch->get(EventSubscriber::class),
            $pouch->get(EventDispatcherInterface::class),
            $pouch->get(FactoryCommandLoader::class),
            $pouch->get('CommandList')
        );
    },
]);

unset($rootPath);
