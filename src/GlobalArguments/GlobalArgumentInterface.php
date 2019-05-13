<?php

declare(strict_types=1);

namespace Dockr\GlobalArguments;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

interface GlobalArgumentInterface
{
    /**
     * Returns InputArgument instance to add to definition.
     *
     * @return \Symfony\Component\Console\Input\InputArgument|null
     */
    public function getArgument(): ?InputArgument;

    /**
     * Returns InputOption instance to add to definition.
     *
     * @return \Symfony\Component\Console\Input\InputArgument|null
     */
    public function getOption(): ?InputOption;
}
