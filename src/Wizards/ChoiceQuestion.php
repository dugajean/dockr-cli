<?php

namespace Dicker\Wizards;

use Symfony\Component\Console\Question\Question as SymfonyQuestion;
use Symfony\Component\Console\Question\ChoiceQuestion as SymfonyChoiceQuestion;

class ChoiceQuestion extends Question implements QuestionInterface
{
    /**
     * @var array
     */
    protected $choices;

    /**
     * @var bool
     */
    protected $multiChoice;

    /**
     * ChoiceQuestion constructor.
     *
     * @param string $question
     * @param array  $choices
     * @param int    $default
     * @param bool   $multi
     */
    public function __construct($question, array $choices, $default = null, $multi = false)
    {
        $this->choices = $choices;
        $this->multiChoice = $multi;

        parent::__construct($question, $default);
    }

    /**
     * Display the question to the user.
     *
     * @return \Dicker\Wizards\Question
     */
    public function render()
    {
        $question = new SymfonyChoiceQuestion($this->question, $this->choices, $this->default);

        $this->includeValidators($question);

        if ($this->multiChoice) {
            $question->setMultiselect(true);
        }

        $this->answer = $this->storeAnswer($question);

        return $this;
    }

    /**
     * Prompts user for input and saves the answer.
     *
     * @param \Symfony\Component\Console\Question\Question $question
     *
     * @return mixed
     */
    protected function storeAnswer(SymfonyQuestion $question)
    {
        $answer = parent::storeAnswer($question);

        return ctype_digit($answer) ? $this->choices[$answer] : $answer;
    }

    /**
     * Appends the default to the question in brackets.
     *
     * @return void
     */
    protected function includeDefault()
    {
        if ($this->default !== null) {
            $default = $this->choices[$this->default];
            $this->question = "{$this->question} [{$default}]";
        }
    }
}
