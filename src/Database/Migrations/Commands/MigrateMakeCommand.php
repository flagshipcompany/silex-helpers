<?php
namespace Flagship\Components\Helpers\Database\Migrations\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Flagship\Components\Helpers\Database\Migrations\MigrationCreator;

class MigrateMakeCommand extends Command
{
    protected $app;

    public function __construct($app)
    {
        parent::__construct();

        $this->app = $app;
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
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $migration = $input->getArgument('migration');

        $this->defaults = MigrationConf::$default;
        $options = [
            'path' => $this->getPath($input->getOption('env'), $input->getOption('path')),
        ];

        $creator = new MigrationCreator(
            $migration,
            $options
        );

        $creator->create();
    }

    protected function getPath($environment, $path) {
        if ($path) {
            return $path;
        }

        if ($environment && $environment != 'dev') {
            return DriverManager::getConnection($this->app[$environment]['migrations.path'], new \Doctrine\DBAL\Configuration());
        }

        if (isset($this->app['migrations.path'])) {
            return $this->app['migrations.path'];
        }

        return $this->defaults['migrations.path'];
    }
}
