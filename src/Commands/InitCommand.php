<?php

namespace Dicker\Commands;

use Dicker\Validators\ValidateNotEmpty;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Finder\Finder;

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
    protected $cacheStore;
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
        $this->performReplacements();
        $this->storeConfig();
    }

    /**
     * Store JSON config file with all the data.
     *
     * @return bool
     */
    public function storeConfig()
    {
        $config = json_encode([
            'project-name' => $this->projectName,
            'project-domain' => $this->projectDomain,
            'webserver' => $this->webServer,
            'cache-store' => $this->cacheStore,
            'php-version' => $this->phpVersion,
            'php-extension' => $this->phpExtensions
        ], JSON_PRETTY_PRINT);

        return (bool)file_put_contents('./dicker.json', $config);
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
        $this->askCacheStore();
        $this->askPhpVersion();
        $this->askPhpExtensions();
    }

    /**
     * Question
     *
     * @return void
     */
    protected function askProjectName()
    {
        $projectNameQuestion = new Question('Please enter the name of the project: ');
        $projectNameQuestion->setValidator((new ValidateNotEmpty('Project Name'))->callback());
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
        $defaultDomain = str_replace(' ', '-', strtolower($this->projectName)).'.';
        $domainQuestion = new Question("Please enter the domain for the project [{$defaultDomain}local]: ", $defaultDomain.'local');
        $domainQuestion->setAutocompleterValues([$defaultDomain]);

        $this->projectDomain = $this->questionHelper->ask($this->input, $this->output, $domainQuestion);

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
    protected function askCacheStore()
    {
        $cacheStoreQuestion = new ChoiceQuestion(
            'Please select the cache store you want your project to run on [redis]: ',
            SwitchCacheCommand::CACHE_STORES, 0
        );

        $this->cacheStore = $this->questionHelper->ask($this->input, $this->output, $cacheStoreQuestion);
        $this->cacheStore = is_numeric($this->cacheStore) ?
            SwitchCacheCommand::CACHE_STORES[$this->cacheStore] :
            $this->cacheStore;

        $this->outputAnswer($this->cacheStore);
    }

    /**
     * Question
     *
     * @return void
     */
    protected function askPhpVersion()
    {
        $phpVersionQuestion = new ChoiceQuestion(
            'Please select the PHP version you want your project to run on [7.2]: ',
            SwitchPhpCommand::PHP_VERSIONS, 2
        );

        $this->phpVersion = $this->questionHelper->ask($this->input, $this->output, $phpVersionQuestion);
        $this->phpVersion = ctype_digit($this->phpVersion) ?
            SwitchPhpCommand::PHP_VERSIONS[$this->phpVersion] :
            $this->phpVersion;

        $this->outputAnswer($this->phpVersion);
    }

    /**
     * Question
     *
     * @return void
     */
    protected function askPhpExtensions()
    {
        $availableExtensionKeys = array_keys(ExtensionEnableCommand::AVAILABLE_EXTENSIONS);

        $phpExtensionsQuestions = new ChoiceQuestion(
            'Please choose which PHP extensions should be included in your project (comma separated list): ',
            $availableExtensionKeys
        );

        $phpExtensionsQuestions->setValidator((new ValidateNotEmpty('PHP Extensions'))->callback());
        $phpExtensionsQuestions->setMultiselect(true);

        $plainExtensionNames = array_unique($this->questionHelper->ask($this->input, $this->output, $phpExtensionsQuestions));

        foreach ($plainExtensionNames as $extensionName) {
            $actualExtensionName = ExtensionEnableCommand::AVAILABLE_EXTENSIONS[$extensionName];

            if (strpos($actualExtensionName, '{PHP_VERSION}') !== false) {
                $actualExtensionName = str_replace('{PHP_VERSION}', $this->phpVersion, $actualExtensionName);
            }

            $this->phpExtensions[] = $actualExtensionName;
        }

        $this->outputAnswer(implode(', ', $plainExtensionNames));
    }

    /**
     * Prepare the stubs
     */
    public function performReplacements()
    {
        $finder = pouch()->resolve('stubsFinder');

        foreach ($finder as $file) {
            $folderStructure = './'.$file->getRelativePath();

            if (! file_exists($folderStructure)) {
                mkdir($folderStructure, 0777, true);
            }

            $contents = $this->replacementQuery($file->getContents());
            $fileName = './'.str_replace('.stub', '', $file->getRelativePathname());
            file_put_contents($fileName, $contents);
        }
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
        $vhost = constant(SwitchWebserverCommand::class.'::'.strtoupper($this->webServer).'_CONF');
        $rawPhp = str_replace('.', '', $this->phpVersion);
        $phpExts = implode(' ', $this->phpExtensions);

        return str_replace(
            [
                '{PROJECT_NAME}', '{CACHE_STORE}', '{PROJECT_DOMAIN}', '{WEBSERVER}',
                '{WEBSERVER_VHOST}', '{PHP_VERSION}', '{PHP_VERSION_RAW}', '{PHP_EXTENSIONS}',
            ],
            [
                $this->projectName, $this->cacheStore, $this->projectDomain, $this->webServer,
                $vhost, $this->phpVersion, $rawPhp, $phpExts
            ],
            $haystack
        );
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
        $this->output->writeln("> <info>{$outputText}</info>");
    }
}
