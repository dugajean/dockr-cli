<?php

namespace Dockr\Tests\Command;

use Dockr\Tests\TestCase;
use Dockr\Commands\InitCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class InitCommandTest extends TestCase
{
    public function testExecute()
    {
        $application = new Application();
        $application->add(new InitCommand);

        $command = $application->find('init');

        $commandTester = new CommandTester($command);
        $commandTester->setInputs(['This', 'This', 'This', 'This', 'This', 'This']);
        $commandTester->execute(['command' => $command->getName()]);

        $output = $commandTester->getDisplay();
        var_dump($output);
    }
}
