<?php

namespace Dockr\Commands;

use Dockr\Config;
use Dockr\Questions\Question;
use Dockr\Hooks\HookRegistrar;
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
     * @var HookRegistrar
     */
    protected $hookRegistrar;

    /**
     * User's answers
     */
    protected $answers = [];

    protected $projectName;
    protected $projectDomain;
    protected $webServer;
    protected $cacheStore;
    protected $phpVersion;
    protected $phpExtensions = [];

    /**
     * Command constructor.
     *
     * @param string|null $name
     *
     * @return void
     * @throws \Pouch\Exceptions\NotFoundException
     * @throws \Pouch\Exceptions\PouchException
     */
    public function __construct(string $name = null)
    {
        parent::__construct($name);

        $this->hookRegistrar = pouch()->get(HookRegistrar::class);
    }

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
     * @param InputInterface   $input
     * @param OutputInterface  $output
     */
    protected function init(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->questionHelper = $this->getHelper('question');

        // Register and run pre-hooks
        $this->hookRegistrar->setCommand($this)->register(HookRegistrar::TYPE_PRE);

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

    /**
     * Returns the current output object.
     *
     * @return \Symfony\Component\Console\Output\OutputInterface
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * Register and run post-hooks
     *
     * @return void
     */
    public function __destruct()
    {
        $this->hookRegistrar->register(HookRegistrar::TYPE_POST);
    }
}
