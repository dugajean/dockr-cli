<?php

namespace Dockr\Commands;

use Dockr\Questions\Question;
use Dockr\Questions\ChoiceQuestion;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'init';

    /**
     * Configure the command
     *
     * @return void
     */
    protected function configure()
    {
        $this->setDescription('Initialize docker-compose')
            ->setHelp('Start an initialization wizard to setup docker-compose for your project.');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int|void|null
     * @throws \Pouch\Exceptions\NotFoundException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $this->runWizard();
        $this->performReplacements();
        $configStored = $this->storeConfig();

        if ($configStored) {
            $this->output->writeln(color('green', 'Successfully initialized Dockr for this directory!'));
        } else {
            $this->output->writeln(color('red', 'Something went wrong while initializing Dockr. Please try again.'));
        }
    }

    /**
     * Store JSON config file with all the data.
     *
     * @return bool
     */
    public function storeConfig()
    {
        $set = $this->config->set([
            'project-name' => $this->projectName,
            'project-domain' => $this->projectDomain,
            'web-server' => $this->webServer,
            'cache-store' => $this->cacheStore,
            'php-version' => $this->phpVersion,
            'php-extensions' => $this->phpExtensions
        ]);

        if ($set) {
            $this->output->writeln('Configuration file has been saved under dockr.json.');
        }

        return $set;
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
        $this->projectName = (new Question('Please enter the name of the project: '))
            ->setValidators(['not_empty'])
            ->render()
            ->outputAnswer()
            ->getAnswer();
    }

    /**
     * Question
     *
     * @return void
     */
    protected function askProjectDomain()
    {
        $defaultDomain = str_replace(' ', '-', strtolower($this->projectName)).'.';

        $this->projectDomain = (new Question('Please enter the domain for the project: ', $defaultDomain . 'local'))
            ->setAutocomplete([$defaultDomain])
            ->render()
            ->outputAnswer()
            ->getAnswer();
    }

    /**
     * Question
     *
     * @return void
     */
    protected function askWebServer()
    {
        $this->webServer = (new ChoiceQuestion(
            'Please select the webserver you want your project to run on: ',
            SwitchWebServerCommand::getChoices(), 0
        ))
            ->render()
            ->outputAnswer()
            ->getAnswer();
    }

    /**
     * Question
     *
     * @return void
     */
    protected function askCacheStore()
    {
        $this->cacheStore = (new ChoiceQuestion(
            'Please select the cache store you want your project to run on: ',
            SwitchCacheStoreCommand::getChoices(), 0
        ))
            ->render()
            ->outputAnswer()
            ->getAnswer();
    }

    /**
     * Question
     *
     * @return void
     */
    protected function askPhpVersion()
    {
        $this->phpVersion = (new ChoiceQuestion(
            'Please select the PHP version you want your project to run on',
            SwitchPhpVersionCommand::getChoices(), 2
        ))
            ->render()
            ->outputAnswer()
            ->getAnswer();
    }

    /**
     * Question
     *
     * @return void
     */
    protected function askPhpExtensions()
    {
        $question = (new ChoiceQuestion(
            'Please choose which PHP extensions should be included in your project (comma separated list): ',
            array_keys(ExtensionEnableCommand::$availableExtensions), null, true
        ))->render();

        $question->adjustAnswer(function ($choices) {
            $resultArray = [];
            foreach ($choices as $extensionName) {
                $actualExtensionName = ExtensionEnableCommand::$availableExtensions[$extensionName];
                if (strpos($actualExtensionName, '{PHP_VERSION}') !== false) {
                    $actualExtensionName = str_replace('{PHP_VERSION}', $this->phpVersion, $actualExtensionName);
                }
                ExtensionEnableCommand::$availableExtensions[$extensionName] = $actualExtensionName;
                $resultArray[] = $extensionName;
            }
            return array_unique($resultArray);
        })->outputAnswer();

        $this->phpExtensions = array_map(function($item) {
            return ExtensionEnableCommand::$availableExtensions[$item];
        }, $question->getAnswer());
    }

    /**
     * Prepare the stubs
     *
     * @return void
     * @throws \Pouch\Exceptions\NotFoundException
     */
    protected function performReplacements()
    {
        $finder = pouch()->resolve('stubs_finder');

        foreach ($finder as $file) {
            $folderStructure = current_path($file->getRelativePath());

            if (!file_exists($folderStructure)) {
                mkdir($folderStructure, 0777, true);
            }

            $contents = $this->replacementQuery($file->getContents());
            $fileName = current_path(str_replace('.stub', '', $file->getRelativePathname()));
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
        $vhost = constant(SwitchWebServerCommand::class . '::' . strtoupper($this->webServer) . '_CONF');
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
}
