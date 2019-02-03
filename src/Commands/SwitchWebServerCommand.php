<?php

namespace Dockr\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SwitchWebServerCommand extends Command
{
    const NGINX_CONF  = '/etc/nginx/conf.d/default.conf';
    const APACHE_CONF = '/usr/local/apache2/conf/extra/httpd-vhosts.conf';

    /**
     * Configure the command
     *
     * @return void
     */
    protected function configure()
    {
        $options = implode(', ', self::getOptions());

        $this
            ->setName('switch:webserver')
            ->setDescription('Switch the webserver powering this project.')
            ->setHelp("Switches the webserver of a project previously setup with Dockr. Allowed values are: {$options}")
            ->setDefinition(
                new InputDefinition([
                    new InputArgument('new value', InputArgument::REQUIRED, 'The new webserver you want to set'),
                ])
            );
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int|void|null
     * @throws \Pouch\Exceptions\NotFoundException
     * @throws \Pouch\Exceptions\PouchException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        parent::basicReplacement(function ($newValue) {
            $vhostOld = constant(self::class . '::' . strtoupper($this->webServer) . '_CONF');
            $vhostNew = constant(self::class . '::' . strtoupper($newValue) . '_CONF');
            $this->replacements[(string)$vhostOld] = (string)$vhostNew;
        });
    }

    /**
     * Return options
     *
     * @return array
     */
    public static function getOptions()
    {
        return ['apache', 'nginx'];
    }
}
