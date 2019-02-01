<?php

namespace Dicker\Validators;

/**
 * Class ValidateNotEmpty
 *
 * @package \Dicker\Validators
 */
class ValidateNotEmpty
{
    /**
     * @param $answer
     *
     * @return mixed
     */
    public function __invoke($answer)
    {
        if (empty($answer) && !is_numeric($answer)) {
            throw new \RuntimeException(
                'The project name cannot be empty.'
            );
        }

        return $answer;
    }
}
