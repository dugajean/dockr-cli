<?php

declare(strict_types=1);

namespace Dockr\Commands;

use function Dockr\Helpers\comma_list;
use Symfony\Component\Console\Input\InputArgument;

class SwitchWebServerCommand extends SwitchCommand
{
    const NGINX_CONF  = '/etc/nginx/conf.d/default.conf';
    const APACHE_CONF = '/usr/local/apache2/conf/extra/httpd-vhosts.conf';

    /**
     * Configure the command
     *
     * @return void
     */
    protected function configure(): void
    {
        $options = comma_list(self::getChoices());

        $this
            ->setName('switch:webserver')
            ->setDescription('Switch the webserver powering this project')
            ->setHelp("Switches the webserver of a project previously setup with Dockr. Allowed values are: {$options}")
            ->addArgument($this->getArgument(), InputArgument::REQUIRED, 'The new webserver you want to set');
    }

    /**
     * Retrieves the appropriate constant.
     *
     * @param string $webServer
     *
     * @return string
     */
    public static function getConf(string $webServer): string
    {
        $constant = 'self::' . strtoupper($webServer) . '_CONF';

        return defined($constant) ? constant($constant) : self::APACHE_CONF;
    }

    /**
     * Return options
     *
     * @return array
     */
    public static function getChoices(): array
    {
        return ['apache', 'nginx'];
    }
}
