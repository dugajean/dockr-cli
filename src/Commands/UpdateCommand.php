<?php

namespace Dockr\Commands;

use PHAR;
use Humbug\SelfUpdate\Updater;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class UpdateCommand extends Command
{
    /**
     * @var \Humbug\SelfUpdate\Updater
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
            ->setDescription("Update {$this->getLocalPharName()} to most recent stable build.");
        ;
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $result = $this->updater->update();

        if ($result) {
            $output->writeln(
                color('green', "Your PHAR has been updated from '{$this->updater->getOldVersion()}' to '{$this->updater->getNewVersion()}'.")
            );
        } else {
            $output->writeln(color('green', 'Your PHAR is already up to date.'));
        }
    }

    /**
     * @return string
     */
    private function getLocalPharName()
    {
        return basename(PHAR::running());
    }
}
