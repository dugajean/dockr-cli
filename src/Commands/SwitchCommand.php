<?php

namespace Dockr\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
     * Return options
     *
     * @return array
     */
    abstract public static function getChoices();

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int|void|null
     * @throws \Pouch\Exceptions\NotFoundException
     * @throws \Pouch\Exceptions\PouchException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $this->currentProp = $this->getSwitchProperty();
        $this->newValue = $this->input->getArgument('new value');

        if (method_exists($this, 'addReplacements')) {
            $this->addReplacements();
        }

        $this->validate();
        $this->performReplacements();

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

        if ($this->newValue == $this->{$this->currentProp}) {
            throw new \RuntimeException("The {$this->newValue} {$currentProp} is already in use. Please try a different value.");
        }

        return $this;
    }

    /**
     * Set a new replacement query.
     *
     * @param string $search
     * @param string $replace
     *
     * @return $this
     */
    public function setReplacement($search, $replace)
    {
        $this->replacements[(string)$search] = (string)$replace;

        return $this;
    }

    /**
     * Replace the files
     *
     * @throws \Pouch\Exceptions\NotFoundException
     * @throws \Pouch\Exceptions\PouchException
     */
    protected function performReplacements()
    {
        $finder = pouch()->get('docker_finder');

        foreach ($finder as $file) {
            $contents = $this->replacementQuery($file->getContents());
            $fileName = current_path($file->getRelativePathname());
            file_put_contents($fileName, $contents);
        }

        $this->config->set(snake_case($this->currentProp, '-'), $this->newValue);
    }

    /**
     * Performs the actual string replacement for all files.
     *
     * @param $haystack
     *
     * @return string
     */
    protected function replacementQuery($haystack)
    {
        $this->replacements[$this->{$this->currentProp}] = $this->newValue;

        $searches = array_keys($this->replacements);
        $replacements = array_values($this->replacements);

        return str_replace($searches, $replacements, $haystack);
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
}
