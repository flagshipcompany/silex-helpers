<?php
namespace Flagship\Components\Helpers\Database\Commands\Migrations;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Flagship\Components\Helpers\Database\Migrations\MigrationCreator;

class MigrateMakeCommand extends Command
{
    protected $app;
    protected $path;

    public function __construct($app, $environment)
    {
        parent::__construct();

        $this->app = $app;
        $this->path = $this->getOption('path')?: $this->app['migrations.path'];
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
               'If not set, the migrations files path will use application default. $app[\'migrations.path\']'
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

        $options = [
            'path' => $this->path,
        ];

        $creator = new MigrationCreator(
            $migration,
            $options
        );

        $creator->create();
    }
}
