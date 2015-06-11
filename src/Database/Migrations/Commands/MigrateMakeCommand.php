<?php
namespace Flagship\Components\Helpers\Database\Migrations\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Flagship\Components\Helpers\Database\Migrations\MigrationCreator;
use Flagship\Components\Helpers\Database\Migrations\Configs\Configuration as MigrationConf;

class MigrateMakeCommand extends Command
{
    protected $app;
    protected $environment;

    public function __construct($app, $environment)
    {
        parent::__construct();

        $this->app = $app;
        $this->environment = $environment;
    }

    protected function configure()
    {
        $this
            ->setName('migrate:make')
            ->setDescription('Make new migration')
            ->addArgument(
                'migration',
                InputArgument::OPTIONAL,
                'Do you want to migrate up(down)?'
            )
            ->addOption(
               'path',
               null,
               InputOption::VALUE_OPTIONAL,
               'If not set, the migrations files path will use application default.'
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
        $migration = $input->getArgument('migration');

        $this->defaults = MigrationConf::$default;
        $options = [
            'path' => $this->getPath($input->getOption('path')),
        ];

        $creator = new MigrationCreator(
            $migration,
            $options
        );

        $creator->create();
    }

    protected function getPath($path) {
        if ($path) {
            return $path;
        }

        if (isset($this->app[$this->environment])) {
            return $this->app[$environment]['migrations.path'];
        }

        if (isset($this->app['migrations.path'])) {
            return $this->app['migrations.path'];
        }

        return $this->defaults['migrations.path'];
    }
}
