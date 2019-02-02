<?php

namespace Dicker\Validators;

class ValidateNotEmpty extends AbstractValidator
{
    /**
     * @inheritdoc
     */
    public function callback()
    {
        return function ($answer) {
            if (empty($answer) && !is_numeric($answer)) {
                throw new \RuntimeException("The {$this->field} field cannot be empty. Please try again.");
            }

            return $answer;
        };
    }
}
