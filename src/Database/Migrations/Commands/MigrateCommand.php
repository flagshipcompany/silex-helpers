<?php
namespace Flagship\Components\Helpers\Database\Migrations\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Flagship\Components\Helpers\Database\Migrations\Migrator;
use Flagship\Components\Helpers\Database\Repositories\MigrationRepository;
use Doctrine\DBAL\DriverManager;


class MigrateCommand extends Command
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
            ->setName('migrate')
            ->setDescription('run migration')
            ->addArgument(
                'mode',
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
               'db',
               null,
               InputOption::VALUE_OPTIONAL,
               'If not set, the migrations files path will use application default.'
            )
            ->addOption(
               'env',
               null,
               InputOption::VALUE_OPTIONAL,
               'If not set, the migrations files path will use application default.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $environment = $input->getOption('env') ?: 'dev';

        $db = $this->getDB($environment, $input->getOption('db'));

        $repository = new MigrationRepository($db);
        $options = [
            'path' => $input->getOption('path') ?: $this->app['migrations.path'],
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

    protected function getDB($environment, $db)
    {
        if ($db) {
            return $this->app['db'][$db];
        }

        $params = $this->app[$environment]['db.options'];

        return DriverManager::getConnection($params, new \Doctrine\DBAL\Configuration());
    }
}
