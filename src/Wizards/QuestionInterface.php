<?php

namespace Dicker\Wizards;

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
     * @return void
     */
    public function render();
}
