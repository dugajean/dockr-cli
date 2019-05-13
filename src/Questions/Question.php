<?php

declare(strict_types=1);

namespace Dockr\Questions;

use Dockr\Validators\ValidatorInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use function Dockr\Helpers\{comma_list, studly_case};
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question as SymfonyQuestion;

class Question implements QuestionInterface
{
    /**
     * @var string
     */
    protected $question;

    /**
     * @var string|array
     */
    protected $answer;

    /**
     * @var mixed
     */
    protected $default;

    /**
     * @var string[]
     */
    protected $validators;

    /**
     * @var string[]
     */
    protected $autocompleteValues;

    /**
     * @var \Closure
     */
    protected $answerCallback;

    /**
     * @var InputInterface
     */
    protected static $input;

    /**
     * @var OutputInterface
     */
    protected static $output;

    /**
     * @var \Symfony\Component\Console\Helper\QuestionHelper
     */
    protected static $questionHelper;

    /**
     * AbstractQuestion constructor.
     *
     * @param string $question
     * @param mixed  $default
     */
    public function __construct(string $question, $default = null)
    {
        $this->question = $question;
        $this->default = $default;

        $this->includeDefault();
    }

    /**
     * Display the question to the user.
     *
     * @return $this
     */
    public function render()
    {
        $question = new SymfonyQuestion($this->question, $this->default);

        $this->includeValidators($question);
        $this->includeAutocomplete($question);

        $this->answer = $this->storeAnswer($question);

        return $this;
    }

    /**
     * Returns the inputted answer.
     *
     * @return array|string
     */
    public function getAnswer()
    {
        if ($this->answerCallback) {
            $this->answer = ($this->answerCallback)($this->answer);
        }

        return $this->answer;
    }

    /**
     * Outputs line in green.
     *
     * @return $this
     */
    public function outputAnswer(): self
    {
        $answer = $this->getAnswer();
        $answer = is_array($answer) ? comma_list($answer) : $answer;

        if ($answer && $answer != 'None') {
            self::$output->writeln("- <info>{$answer}</info>");
        }

        return $this;
    }

    /**
     * Alter the answer before it gets outputted or returned.
     *
     * @param \Closure $closure
     *
     * @return $this
     */
    public function adjustAnswer(\Closure $closure): self
    {
        $this->answerCallback = $closure;

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
        if (method_exists($question, 'isMultiselect') && $question->isMultiselect()) {
            // exit(var_dump(self::$input));
        }

        return self::$questionHelper->ask(self::$input, self::$output, $question);
    }

    /**
     * Appends the default to the question in brackets.
     *
     * @return void
     */
    protected function includeDefault(): void
    {
        if ($this->default === null) {
            return;
        }

        if (substr($this->question, -2) == ': ') {
            $this->question = str_replace(': ', '', $this->question);
        }

        $this->question = "{$this->question} [{$this->default}]: ";
    }

    /**
     * Set validators for question.
     *
     * @param string[] $validators
     *
     * @return $this
     */
    public function setValidators(array $validators): self
    {
        $this->validators = $validators;

        return $this;
    }

    /**
     * Set auto-completion options.
     *
     * @param array $autocomplete
     *
     * @return $this
     */
    public function setAutocomplete(array $autocomplete): self
    {
        $this->autocompleteValues = $autocomplete;

        return $this;
    }

    /**
     * Include the validators to the question.
     *
     * @param SymfonyQuestion $question
     * 
     * @return void
     */
    protected function includeValidators(SymfonyQuestion $question): void
    {
        if (!$this->validators) {
            return;
        }

        $validatorNamespace = str_replace('ValidatorInterface', '', ValidatorInterface::class);
        foreach ($this->validators as $validator) {
            $validatorClass = $validatorNamespace . 'Validate' . studly_case($validator);

            if (class_exists($validatorClass)) {
                $question->setValidator(new $validatorClass());
            } else {
                throw new \RuntimeException("Validator '{$validatorClass}' not found.");
            }
        }
    }

    /**
     * Include autocomplete values.
     *
     * @param SymfonyQuestion $question
     * 
     * @return void
     */
    protected function includeAutocomplete(SymfonyQuestion $question): void
    {
        if ($this->autocompleteValues) {
            $question->setAutocompleterValues($this->autocompleteValues);
        }
    }

    /**
     * @param InputInterface   $input
     * @param OutputInterface  $output
     * @param QuestionHelper   $helper
     * 
     * @return void
     */
    public static function setIO(InputInterface $input, OutputInterface $output, QuestionHelper $helper): void
    {
        self::$input = $input;
        self::$output = $output;
        self::$questionHelper = $helper;
    }
}
