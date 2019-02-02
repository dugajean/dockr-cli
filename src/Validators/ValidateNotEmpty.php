<?php

namespace Dicker\Validators;

class ValidateNotEmpty implements ValidatorInterface
{
    /**
     * @inheritdoc
     */
    public function callback()
    {
        return function ($answer) {
            if (empty($answer) && !is_numeric($answer)) {
                throw new \RuntimeException('This field cannot be empty. Please try again.');
            }

            return $answer;
        };
    }
}
