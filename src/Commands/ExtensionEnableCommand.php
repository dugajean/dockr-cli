<?php

namespace Dockr\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExtensionEnableCommand extends Command
{
    public static $availableExtensions = [
        'memcached' => 'php-memcached',
        'mysql' => 'php{PHP_VERSION}-mysql',
        'pgsql' => 'php{PHP_VERSION}-pgsql',
        'redis' => 'php-redis',
        'xdebug' => 'php-xdebug',
        'sqlite' => 'php{PHP_VERSION}-sqlite3',
        'bcmath' => 'php{PHP_VERSION}-bcmath',
        'bz' => 'php{PHP_VERSION}-bz2',
        'dba' => 'php{PHP_VERSION}-dba',
        'enchant' => 'php{PHP_VERSION}-enchant',
        'gd' => 'php{PHP_VERSION}-gd',
        'gearman' => 'php-gearman',
        'gmp' => 'php{PHP_VERSION}-gmp',
        'igbinary' => 'php-igbinary',
        'imagick' => 'php-imagick',
        'imap' => 'php{PHP_VERSION}-imap',
        'interbase' => 'php{PHP_VERSION}-interbase',
        'intl' => 'php{PHP_VERSION}-intl',
        'ldap' => 'php{PHP_VERSION}-ldap',
        'mongodb' => 'php-mongodb',
        'msgpack' => 'php-msgpack',
        'odbc' => 'php{PHP_VERSION}-odbc',
        'phpdbg' => 'php{PHP_VERSION}-phpdbg',
        'pspell' => 'php{PHP_VERSION}-pspell',
        'raphf' => 'php-raphf',
        'recode' => 'php{PHP_VERSION}-recode',
        'snmp' => 'php{PHP_VERSION}-snmp',
        'soap' => 'php{PHP_VERSION}-soap',
        'ssh' => 'php-ssh2',
        'sybase' => 'php{PHP_VERSION}-sybase',
        'tideways' => 'php-tideways',
        'tidy' => 'php{PHP_VERSION}-tidy',
        'xmlrpc' => 'php{PHP_VERSION}-xmlrpc',
        'xsl' => 'php{PHP_VERSION}-xsl',
        'yaml' => 'php-yaml',
        'zmq' => 'php-zmq',
    ];

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * Configure the command
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('ext:enable')
            ->setDescription('Enable one or more PHP modules.')
            ->setHelp('Provide a comma separated list of PHP modules that you want to enable. Use --list to see all available options.')
            ->setDefinition(
                new InputDefinition([
                    new InputArgument('extensions', InputArgument::IS_ARRAY, 'Space separated list of PHP extensions you want to enable'),
                    new InputOption('list', 'l', InputOption::VALUE_NONE, 'Display a list of available PHP extensions'),
                ])
            );
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

        if ($input->getOption('list')) {
            $this->listExtensions();
            return;
        }

        $extensions = $input->getArgument('extensions');

        if (count($extensions) > 0) {

        }
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
    }

    /**
     * Displays a table with all available extensions
     *
     * @return void
     */
    protected function listExtensions()
    {
        $table = new Table($this->output);
        $table->setHeaders(['Available extenions'])->setRows(
            array_map(function ($item) {
                return [$item];
            }, array_keys(self::AVAILABLE_EXTENSIONS))
        );
        $table->render();
    }
}
