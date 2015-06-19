<?php
namespace Flagship\Components\Helpers\Database\Migrations;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Flagship\Components\Helpers\Database\Migrations\Migrator;
use Flagship\Components\Helpers\Database\Migrations\MigrationRepository;


class MigrateCommand extends Command
{
    protected $app;
    protected $db;
    protected $path;

    public function __construct($app)
    {
        parent::__construct();

        $this->app = $app;
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
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->db = $input->getOption('db') ? $this->app['dbs'][$input->getOption('db')] : $this->app['db'];
        $this->path = $input->getOption('path')?: $this->app['migrations.path'];

        $repository = new MigrationRepository($this->db);
        $options = [
            'path' => $this->path,
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
}










