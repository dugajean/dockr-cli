<?php

declare(strict_types=1);

namespace Dockr\Validators;

class ValidateUnique implements ValidatorInterface
{
    /**
     * @inheritdoc
     */
    public function __invoke($answer)
    {
        if (is_array($answer) && $answer != array_unique($answer)) {
            throw new \RuntimeException('The choices must be unique. Please try again.');
        }

        return $answer;
    }
}
