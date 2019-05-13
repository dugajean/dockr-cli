<?php

declare(strict_types=1);

namespace Dockr\Validators;

class ValidateNotEmpty implements ValidatorInterface
{
    /**
     * @inheritdoc
     */
    public function __invoke($answer)
    {
        if (empty($answer) && !is_numeric($answer)) {
            throw new \RuntimeException('This field cannot be empty. Please try again.');
        }

        return $answer;
    }
}
