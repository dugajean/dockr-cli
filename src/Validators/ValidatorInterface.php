<?php

declare(strict_types=1);

namespace Dockr\Validators;

interface ValidatorInterface
{
    /**
     * @param mixed $answer
     *
     * @return mixed
     * @throws \RuntimeException
     */
    public function __invoke($answer);
}
