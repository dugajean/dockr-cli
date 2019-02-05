<?php

namespace Dockr\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;

class SwitchWebServerCommand extends SwitchCommand
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
        $options = comma_list(self::getChoices());

        $this
            ->setName('switch:webserver')
            ->setDescription('Switch the webserver powering this project')
            ->setHelp("Switches the webserver of a project previously setup with Dockr. Allowed values are: {$options}")
            ->setDefinition(
                new InputDefinition([
                    new InputArgument($this->getArgument(), InputArgument::REQUIRED, 'The new webserver you want to set'),
                ])
            );
    }

    /**
     * Adds replacer for the vhost mounting point in docker-compose.yml
     *
     * @param $newValue
     *
     * @return void
     */
    public function addReplacements($newValue)
    {
        $vhostOld = constant(self::class . '::' . strtoupper($this->webServer) . '_CONF');
        $vhostNew = constant(self::class . '::' . strtoupper($newValue) . '_CONF');

        $this->setReplacement($vhostOld, $vhostNew);
    }

    /**
     * Return options
     *
     * @return array
     */
    public static function getChoices()
    {
        return ['apache', 'nginx'];
    }
}
