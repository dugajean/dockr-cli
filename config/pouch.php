<?php

use Pouch\Pouch;
use Dockr\Config;
use Dockr\Commands;
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
    OutputInterface::class => function (): OutputInterface {
        return new ConsoleOutput;
    },
    Application::class => function (): Application {
        return new Application('Dockr CLI', '@package_version@');
    },
    EventDispatcherInterface::class => function (): EventDispatcher {
        return new EventDispatcher;
    },
    FactoryCommandLoader::class => function (ContainerInterface $pouch): FactoryCommandLoader {
        return new FactoryCommandLoader($pouch->get('aliasCommands'));
    },
    Updater::class => function (): Updater {
        $file = file_exists('bin/dockr.phar') ? 'bin/dockr.phar' : null;

        $updater = new Updater($file, false);
        $updater->setStrategy(Updater::STRATEGY_GITHUB);
        $updater->getStrategy()->setPackageName('dugajean/dockr-cli');
        $updater->getStrategy()->setPharName('dockr.phar');

        return $updater;
    },
    'stubsFinder' => pouch()->named(function () use ($rootPath): Finder {
        return (new Finder())->in($rootPath . 'stubs')->name('*.stub')->ignoreDotFiles(false);
    }),
    'aliasCommands' => pouch()->named(function (ContainerInterface $pouch): array {
        
        $commandInstances = [];

        $config = $pouch->get(Config::class);
        $commands = $config->get('aliases');

        foreach ((array)$commands as $alias => $commandList) {
            $commandInstances[$alias] = function () use ($alias, $commandList, $pouch, $config) {
                $aliasCommand = new AliasCommand(
                    $alias,
                    $commandList,
                    $pouch->get(OutputInterface::class),
                    $config->getObject(),
                    new Dotenv
                );

                return $aliasCommand->getClass();
            };
        }

        return $commandInstances;
    }),
    'commands' => pouch()->named(function (ContainerInterface $pouch): array {
        return [
            $pouch->get(Commands\InitCommand::class)->getObject(),
            $pouch->get(Commands\SelfUpdateCommand::class)->getObject(),
            $pouch->get(Commands\SwitchWebServerCommand::class)->getObject(),
            $pouch->get(Commands\SwitchPhpVersionCommand::class)->getObject(),
            $pouch->get(Commands\SwitchCacheStoreCommand::class)->getObject(),
        ];
    }),
]);

pouch()->registerNamespaces('Dockr');

unset($rootPath);
