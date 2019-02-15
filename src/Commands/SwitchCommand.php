<?php

namespace Dockr\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function Dockr\Helpers\{camel_case, class_basename, color, snake_case};

abstract class SwitchCommand extends Command
{
    /**
     * @var string
     */
    protected $currentProp;

    /**
     * @var string
     */
    protected $newValue;

    /**
     * @var array
     */
    protected $replacements = [];

    /**
     * @var string
     */
    protected $argument = 'new value';

    /**
     * Return options
     *
     * @return array
     */
    abstract public static function getChoices();

    /**
     * @param InputInterface   $input
     * @param OutputInterface  $output
     *
     * @return int|void|null
     * @throws \Pouch\Exceptions\NotFoundException
     * @throws \Pouch\Exceptions\PouchException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $this->currentProp = $this->getSwitchProperty();
        $this->newValue = $this->input->getArgument($this->getArgument());

        if (method_exists($this, 'addReplacements')) {
            $this->addReplacements($this->newValue);
        }

        $this->validate();
        $this->config->set(snake_case($this->currentProp, '-'), $this->newValue);

        $prop = snake_case($this->currentProp, ' ');
        $this->output->writeln(color('green', "Successfully changed the {$prop} to {$this->newValue}."));
    }

    /**
     * Validates user input.
     *
     * @return $this
     */
    public function validate()
    {
        $currentProp = snake_case($this->currentProp, ' ');

        if (!in_array($this->newValue, static::getChoices())) {
            throw new \RuntimeException("Invalid {$currentProp} inputted. Must be: " . implode(', ', static::getChoices()));
        }

        if ($this->newValue == $this->answers[$this->currentProp]) {
            throw new \RuntimeException("The {$this->newValue} {$currentProp} is already in use. Please try a different value.");
        }

        return $this;
    }

    /**
     * Returns the component making the calls.
     *
     * @return string
     */
    protected function getSwitchProperty()
    {
        $prop = str_replace('Switch', '', class_basename(static::class));
        $prop = str_replace('Command', '', $prop);

        return camel_case($prop);
    }

    /**
     * Returns current argument.
     *
     * @return string
     */
    public function getArgument()
    {
        return $this->argument;
    }
}
