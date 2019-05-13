<?php

namespace Dockr\Commands;

use Dockr\Config;
use Dockr\Questions\Question;
use function Dockr\Helpers\camel_case;
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
    protected $answers = [];

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
        $this->init($input, $output);

        $this->loadConfig();
    }

    /**
     * Init the command.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function init(InputInterface $input, OutputInterface $output)
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
     * @throws \Pouch\Exceptions\PouchException
     */
    protected function loadConfig()
    {
        $this->config = pouch()->get(Config::class);
        $configData = $this->config->get();

        if ($configData === null) {
            return;
        }

        foreach ($configData as $key => $value) {
            $prop = camel_case($key);
            $this->answers[$prop] = $value;
        }
    }
}
