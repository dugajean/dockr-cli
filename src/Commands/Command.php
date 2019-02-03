<?php

namespace Dockr\Commands;

use Dockr\Config;
use Dockr\Questions\Question;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

abstract class Command extends SymfonyCommand
{
    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var \Symfony\Component\Console\Helper\QuestionHelper
     */
    protected $questionHelper;

    /**
     * @var Config
     */
    protected $config;

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
     * User's answers
     */
    protected $projectName;
    protected $projectDomain;
    protected $webServer;
    protected $cacheStore;
    protected $phpVersion;
    protected $phpExtensions = [];

    /**
     * Return options
     *
     * @return array
     */
    abstract public static function getOptions();

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int|void|null
     * @throws \Pouch\Exceptions\NotFoundException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->loadConfig();
        $this->setIO($input, $output);
    }

    /**
     * Set input and output props.
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function setIO(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->questionHelper = $this->getHelper('question');
        $this->currentProp = $this->getSwitchProperty();

        Question::setIO($input, $output, $this->questionHelper);
    }

    /**
     * Loads dockr.json into props.
     *
     * @return void
     * @throws \Pouch\Exceptions\NotFoundException
     */
    protected function loadConfig()
    {
        $this->config = pouch()->resolve(Config::class);
        $configData = $this->config->get();

        foreach ($configData as $key => $value) {
            $prop = camel_case($key);
            $this->{$prop} = $value;
        }
    }

    /**
     * Initiates a basic replacement.
     *
     * @param \Closure|null $postValidation
     *
     * @throws \Pouch\Exceptions\NotFoundException
     * @throws \Pouch\Exceptions\PouchException
     */
    protected function basicReplacement(\Closure $postValidation = null)
    {
        $newValue = $this->input->getArgument('new value');
        $currentProp = snake_case($this->currentProp, ' ');

        if (!in_array($newValue, static::getOptions())) {
            throw new \RuntimeException("Invalid {$currentProp} inputted. Must be: " . implode(', ', static::getOptions()));
        }

        if ($newValue == $this->{$this->currentProp}) {
            throw new \RuntimeException("The {$newValue} {$currentProp} is already in use");
        }

        // Call post validation hook
        if ($postValidation !== null) {
            $postValidation($newValue);
        }

        // Store new value
        $this->newValue = $newValue;

        // Perform replacements now
        $this->performReplacements();

        $prop = snake_case($this->currentProp, ' ');
        $this->output->writeln(color('green', "Successfully changed the {$prop} value to {$newValue}"));
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
    public function replacementQuery($haystack)
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
