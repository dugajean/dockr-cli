<?php

declare(strict_types=1);

namespace Dockr\Commands;

use Humbug\SelfUpdate\Updater;
use function Dockr\Helpers\color;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class UpdateCommand extends Command
{
    /**
     * @var Updater
     */
    private $updater;

    /**
     * @inheritdoc
     */
    public function __construct(Updater $updater)
    {
        parent::__construct();

        $this->updater = $updater;
    }

    /**
     * @inheritdoc
     */
    protected function configure(): void
    {
        $this
            ->setName('update')
            ->setDescription('Update dockr to most recent stable build');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $result = $this->updater->update();

        if ($result) {
            $message = "Dockr has been updated from '{$this->updater->getOldVersion()}' to '{$this->updater->getNewVersion()}'.";
            $output->writeln(color('green', $message));
        } else {
            $output->writeln(color('green', 'Dockr is already up to date.'));
        }
    }
}
