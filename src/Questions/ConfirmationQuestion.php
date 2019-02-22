<?php

namespace Dockr\Questions;

use Symfony\Component\Console\Question\ConfirmationQuestion as SymfonyConfirmationQuestion;

class ConfirmationQuestion extends Question implements QuestionInterface
{
    /**
     * Display the question to the user.
     *
     * @return \Dockr\Questions\Question
     */
    public function render()
    {
        $question = new SymfonyConfirmationQuestion($this->question, $this->default);

        $this->includeValidators($question);

        $this->answer = $this->storeAnswer($question);

        return $this;
    }

    /**
     * Appends the default to the question in brackets.
     *
     * @return void
     */
    protected function includeDefault()
    {
        if ($this->default === null) {
            return;
        }

        if (substr($this->question, -2) == ': ') {
            $this->question = str_replace(': ', '', $this->question);
        }

        $y  = 'y';
        $n  = 'n';
        $yn = $this->default !== false ? strtoupper($y) . '/' . $n : $y . '/' . strtoupper($n);

        $this->question = "{$this->question} [{$yn}]: ";
    }
}
