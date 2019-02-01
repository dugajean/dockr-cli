<?php

namespace Dicker\Commands;

use Dicker\Validators\ValidateNotEmpty;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'init';

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
     * User's answers
     */
    protected $projectName;
    protected $projectDomain;
    protected $webServer;
    protected $phpVersion;
    protected $phpExtensions = [];

    /**
     * Configure the command
     *
     * @return void
     */
    protected function configure()
    {
        $this->setDescription('Initialize docker-compose.')
            ->setHelp('Start an initialization wizard to setup docker-compose for your project.');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setIO($input, $output);
        $this->runWizard();
    }

    /**
     * Set input and output props.
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function setIO(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->questionHelper = $this->getHelper('question');
    }

    /**
     * Ask series of questions and store answers.
     *
     * @return void
     */
    protected function runWizard()
    {
        $this->askProjectName();
        $this->askProjectDomain();
        $this->askWebServer();
        $this->askPhpVersion();
    }

    /**
     * Question
     *
     * @return void
     */
    public function askProjectName()
    {
        $projectNameQuestion = new Question('Please enter the name of the project: ');
        $projectNameQuestion->setValidator(new ValidateNotEmpty());
        $this->projectName = $this->questionHelper->ask($this->input, $this->output, $projectNameQuestion);

        $this->outputAnswer($this->projectName);
    }

    /**
     * Question
     *
     * @return void
     */
    protected function askProjectDomain()
    {
        $defaultDomain = str_replace(' ', '-', strtolower($this->projectName)).'.local';
        $projectDomainQuestion = new Question("Please enter the domain for the project [{$defaultDomain}]: ", $defaultDomain);
        $this->projectDomain = $this->questionHelper->ask($this->input, $this->output, $projectDomainQuestion);

        $this->outputAnswer($this->projectDomain);
    }

    /**
     * Question
     *
     * @return void
     */
    protected function askWebServer()
    {
        $webServerQuestion = new ChoiceQuestion(
            'Please select the webserver you want your project to run on [apache]: ',
            SwitchWebserverCommand::WEBSERVERS, 0
        );

        $this->webServer = $this->questionHelper->ask($this->input, $this->output, $webServerQuestion);
        $this->webServer = is_numeric($this->webServer) ?
            SwitchWebserverCommand::WEBSERVERS[$this->webServer] :
            $this->webServer;

        $this->outputAnswer($this->webServer);
    }

    /**
     * Question
     *
     * @return void
     */
    public function askPhpVersion()
    {
        $phpVersionQuestion = new ChoiceQuestion(
            'Please select the PHP version you want your project to run on [7.2]: ',
            SwitchPhpCommand::PHP_VERSIONS, 2
        );

        $phpVersionQuestion->setValidator(new ValidateNotEmpty());

        $this->phpVersion = $this->questionHelper->ask($this->input, $this->output, $phpVersionQuestion);
        $this->phpVersion = is_numeric($this->phpVersion) ?
            SwitchPhpCommand::PHP_VERSIONS[$this->phpVersion] :
            $this->phpVersion;

        $this->outputAnswer($this->phpVersion);
    }

    /**
     * Outputs line in green.
     *
     * @param $outputText
     *
     * @return void
     */
    protected function outputAnswer($outputText)
    {
        $this->output->writeln('> <info>'.$outputText.'</info>');
    }
}
