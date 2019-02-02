<?php

namespace Dockr\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SwitchWebserverCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'switch:webserver';

    const WEBSERVERS = [
        'apache', 'nginx'
    ];

    const APACHE_CONF = '/usr/local/apache2/conf/extra/httpd-vhosts.conf';
    const NGINX_CONF = '/etc/nginx/conf.d/default.conf';

    /**
     * Configure the command
     *
     * @return void
     */
    protected function configure()
    {
        $options = implode(',', self::WEBSERVERS);

        $this->setDescription('Switch the webserver powering this project.')
            ->setHelp("Switches the webserver of a project previously setup with Dockr. Allowed values are: {$options}");
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // ...
    }
}
