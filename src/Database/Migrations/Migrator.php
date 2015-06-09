<?php

namespace Flagship\Components\Helpers\Database\Migrations;
use Flagship\Components\Helpers\Database\Repositories\MigrationRepository;

class Migrator
{
    protected $options;
    protected $repository;

    protected $migrations;
    protected $output;

    public function __construct(MigrationRepository $repository, $options = [], $output)
    {
        $this->repository = $repository;

        $this->options = $options;

        $this->output = $output;

        if (! $this->repository->exist()) {
            $this->repository->createMigrationTable();
        }
    }

    public function run()
    {
        $files = $this->getMigrationFiles();

        // Once we grab all of the migration files for the path, we will compare them
        // against the migrations that have already been run for this package then
        // run each of the outstanding migrations against a database connection.
        $ran = $this->repository->getRan();
        
        $this->migrations = array_diff($files, $ran);

        $this->requireFiles($this->options['path']);

        $this->runMigrationList();

    }

    /**
     * Get all of the migration files in option path.
     *
     * @return array
     */
    public function getMigrationFiles()
    {
        $files = glob($this->options['path'] . '/*_*.php');

        if (!$files) {
            return [];
        }

        $files = array_map(function($file)
        {
            return str_replace('.php', '', basename($file));
        }, $files);

        sort($files);

        return $files;
    }

    /**
     * Require in all the migration files in a given path.
     *
     * @param  string  $path
     * @return void
     */
    public function requireFiles($path)
    {
        foreach ($this->migrations as $file) {
            require_once($path.'/'.$file.'.php');
        }
    }

    /**
     * Run an array of migrations.
     *
     * @param  array  $migrations
     * @param  bool   $pretend
     * @return void
     */
    public function runMigrationList()
    {
        // First we will just make sure that there are any migrations to run. If there
        // aren't, we will just make a note of it to the developer so they're aware
        // that all of the migrations have been run against this database system.
        if (count($this->migrations) == 0)
        {
            $this->note('<info>Nothing to migrate.</info>');
            return;
        }

        $batch = $this->repository->getNextBatchNumber();

        // Once we have the array of migrations, we will spin through them and run the
        // migrations "up" so the changes are made to the databases. We'll then log
        // that the migration was run so we don't repeat it next time we execute.
        foreach ($this->migrations as $file)
        {
            $this->runUp($file, $batch);
        }
    }

    /**
     * Rollback the last migration operation.
     *
     * @return int
     */
    public function rollback()
    {
        $this->notes = [];

        // We want to pull in the last batch of migrations that ran on the previous
        // migration operation. We'll then reverse those migrations and run each
        // of them "down" to reverse the last migration "operation" which ran.
        $this->migrations = $this->repository->getLast();

        if (count($this->migrations) == 0)
        {
            $this->note('<info>Nothing to rollback.</info>');

            return count($this->migrations);
        }

        $this->requireFiles($this->options['path']);

        // We need to reverse these migrations so that they are "downed" in reverse
        // to what they run on "up". It lets us backtrack through the migrations
        // and properly reverse the entire database schema operation that ran.
        foreach ($this->migrations as $migration)
        {
            $this->runDown($migration);
        }

        return count($this->migrations);
    }

    /**
     * Resolve a migration instance from a file.
     *
     * @param  string  $file
     * @return object
     */
    public function resolve($file)
    {
        $arr = array_slice(explode('_', $file), 4);
        $arr = array_map(function($item){
            return ucfirst($item); 
        }, $arr);

        $class = implode('', $arr);

        return new $class;
    }

    /**
     * Get the notes for the last operation.
     *
     * @return array
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Run "down" a migration instance.
     *
     * @param  object  $migration
     * @return void
     */
    protected function runDown($migration)
    {
        // First we will get the file name of the migration so we can resolve out an
        // instance of the migration.
        $instance = $this->resolve($migration);
        

        $this->repository->migrate($instance->down());
        // Once we have successfully run the migration "down" we will remove it from
        // the migration repository so it will be considered to have not been run
        // by the application then will be able to fire by any later operation.
        $this->repository->delete($migration);

        $this->note("<info>Rolled back:</info> $migration");

    }

    /**
     * Run "up" a migration instance.
     *
     * @param  string  $file
     * @param  int     $batch
     * @return void
     */
    protected function runUp($file, $batch)
    {
        // First we will resolve a "real" instance of the migration class from this
        // migration file name. Once we have the instances we can run the actual
        // command such as "up" or "down", or we can just simulate the action.
        $migration = $this->resolve($file);

        $this->repository->migrate($migration->up());

        // Once we have run a migrations class, we will log that it was run in this
        // repository so that we don't try to run it next time we do a migration
        // in the application. A migration repository keeps the migrate order.
        $this->repository->log($file, $batch);

        $this->note("<info>Migrated:</info> $file");
    }

    /**
     * Raise a note event for the migrator.
     *
     * @param  string  $message
     * @return void
     */
    protected function note($message)
    {
        $this->output->writeln($message);
    }




}