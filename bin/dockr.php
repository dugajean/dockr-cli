<?php

require __DIR__ . '/../vendor/autoload.php';

try {
    (new \Dockr\App)->run();
} catch (\Exception $e) {
    echo $e->getMessage() . "\n";
    exit(1);
}
