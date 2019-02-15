<?php

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
    public function __construct()
    {
        parent::__construct();

        $this->updater = pouch()->get(Updater::class);
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('update')
            ->setDescription("Update dockr to most recent stable build.");
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $result = $this->updater->update();

        if ($result) {
            $output->writeln(
                color('green', "Dockr has been updated from '{$this->updater->getOldVersion()}' to '{$this->updater->getNewVersion()}'.")
            );
        } else {
            $output->writeln(color('green', 'Dockr is already up to date.'));
        }
    }
}
