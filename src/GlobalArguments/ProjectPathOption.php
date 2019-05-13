<?php

declare(strict_types=1);

namespace Dockr\GlobalArguments;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ProjectPathOption implements GlobalArgumentInterface
{
    /**
     * Returns InputOption instance to add to definition.
     *
     * @return \Symfony\Component\Console\Input\InputArgument|null
     */
    public function getOption(): ?InputOption
    {
        $optionDesc = 'Path to the project which holds dockr.json. Defaults to the currect directory.';

        return new InputOption('project-path', null, InputOption::VALUE_REQUIRED, $optionDesc);
    }

    /**
     * Returns InputArgument instance to add to definition.
     *
     * @return \Symfony\Component\Console\Input\InputArgument|null
     */
    public function getArgument(): ?InputArgument
    {
        return null;
    }
}
