<?php

use Pouch\Pouch;
use Dockr\Config;
use Symfony\Component\Finder\Finder;

Pouch::bootstrap(__DIR__ . '/../');

pouch()->bind('stubsFinder', function () {
    return (new Finder())->in(__DIR__ . '/../stubs')->name('*.stub')->ignoreDotFiles(false);
});

pouch()->bind(Config::class, function () {
    return new Config;
});
