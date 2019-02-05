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
     * User's answers
     */
    protected $projectName;
    protected $projectDomain;
    protected $webServer;
    protected $cacheStore;
    protected $phpVersion;
    protected $phpExtensions = [];

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

        if ($configData === null) {
            return;
        }

        foreach ($configData as $key => $value) {
            $prop = camel_case($key);
            $this->{$prop} = $value;
        }
    }
}
