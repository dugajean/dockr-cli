<?php

use Pouch\Pouch;
use Dockr\Config;
use Symfony\Component\Finder\Finder;

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
    Config::class => function () {
        return new Config;
    }
]);
