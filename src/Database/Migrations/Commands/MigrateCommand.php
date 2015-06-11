<?php
namespace Flagship\Components\Helpers\Database\Migrations\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Flagship\Components\Helpers\Database\Migrations\Migrator;
use Flagship\Components\Helpers\Database\Migrations\Configs\Configuration as MigrationConf;
use Flagship\Components\Helpers\Database\Repositories\MigrationRepository;
use Doctrine\DBAL\DriverManager;


class MigrateCommand extends Command
{
    protected $app;

    public function __construct($app, $environment)
    {
        parent::__construct();

        $this->app = $app;
        $this->environment = $environment;
    }

    protected function configure()
    {
        $this
            ->setName('migrate')
            ->setDescription('run migration')
            ->addArgument(
                'mode',
                InputArgument::OPTIONAL,
                'migrate up/down'
            )
            ->addOption(
               'path',
               null,
               InputOption::VALUE_OPTIONAL,
               'If not set, the migrations files path will use application default. If application default is not accessible, the default path will be "./var/migrations"'
            )
            ->addOption(
               'db',
               null,
               InputOption::VALUE_OPTIONAL,
               'If not set, the migrations files path will use application default. If application default is not accessible, the default can be found in configuration class.'
            )
            ->addOption(
               'env',
               null,
               InputOption::VALUE_OPTIONAL,
               'If not set, the migrations files path will use application default. If application default is not accessible, the default can be found in configuration class.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->defaults = MigrationConf::$default;

        $db = $this->getDB($input->getOption('db'));

        $repository = new MigrationRepository($db);
        $options = [
            'path' => $this->getPath($input->getOption('path')),
        ];

        $migrator = new Migrator(
            $repository, 
            $options,
            $output
        );

        $mode = $input->getArgument('mode')?: 'up';

        if ($mode == 'up') {
            $migrator->run();
        } else {
            $migrator->rollback();
        }
    }

    protected function getPath($path) {
        if ($path) {
            return $path;
        }

        if (isset($this->app[$this->environment])) {
            return $this->app[$this->environment]['migrations.path'];
        }

        if (isset($this->app['migrations.path'])) {
            return $this->app['migrations.path'];
        }

        return $this->defaults['migrations.path'];
    }

    protected function getDB($db)
    {
        $target = isset($this->app['dbs']) ? 'dbs' : 'db';

        if ($db) {
            // return a DBAL connection
            return $this->app[$target][$db];
        }

        if ($target == 'dbs' && count($this->app['dbs']) > 0) {
            return array_values($this->app['dbs'])[0]; // return first db connection
        }

        if (isset($this->app[$this->environment])) {
            return DriverManager::getConnection($this->app[$environment]['db.options'], new \Doctrine\DBAL\Configuration());
        }

        if (isset($this->app['db.options'])) {
            return DriverManager::getConnection($this->app['db.options'], new \Doctrine\DBAL\Configuration());
        }

        return DriverManager::getConnection($this->defaults['db.options'], new \Doctrine\DBAL\Configuration());
    }
}










