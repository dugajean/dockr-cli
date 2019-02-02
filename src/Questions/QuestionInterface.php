<?php

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
     * @return void
     */
    public function render();
}
