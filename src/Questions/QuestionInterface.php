<?php

declare(strict_types=1);

namespace Dockr\Questions;

interface QuestionInterface
{
    /**
     * Returns the inputted answer.
     *
     * @return array|string
     */
    public function getAnswer();

    /**
     * Display the question to the user.
     *
     * @return self
     */
    public function render(): Question;
}
