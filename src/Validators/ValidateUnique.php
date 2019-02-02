<?php

namespace Dicker\Validators;

class ValidateUnique implements ValidatorInterface
{
    /**
     * @inheritdoc
     */
    public function callback()
    {
        return function ($answer) {
            if (is_array($answer) && $answer != array_unique($answer)) {
                throw new \RuntimeException('The choices must be unique. Please try again.');
            }

            return $answer;
        };
    }
}
