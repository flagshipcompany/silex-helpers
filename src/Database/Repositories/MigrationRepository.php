<?php

namespace Flagship\Components\Helpers\Database\Repositories;
use Doctrine\DBAL\Connection as Database;

class MigrationRepository
{
	public function __construct(Database $db)
    {
        $this->db = $db;
        $this->table = 'migrations';
    }

    /**
	 * Get the ran migrations.
	 *
	 * @return array
	 */
    public function getRan() 
    {
    	$q = $this->db->createQueryBuilder();

    	$results = $q->select('migration')->from($this->table)->execute()->fetchAll();

    	return array_column($results, 'migration');
    }

    /**
	 * Get the next migration batch number.
	 *
	 * @return int
	 */
	public function exist()
	{
		$params = $this->db->getParams();

		$q = $this->db->createQueryBuilder();

		$result = $q
			->select('count(table_name) as c')
			->from('information_schema.tables')
			->where('table_schema = \'' . $params['dbname'] . '\' AND table_name = \'migrations\'')
			->execute()->fetch();

		return ($result['c'] > 0);
	}

    /**
	 * Get the next migration batch number.
	 *
	 * @return int
	 */
	public function getNextBatchNumber()
	{
		return $this->getLastBatchNumber() + 1;
	}

	/**
	 * Get the last migration batch number.
	 *
	 * @return int
	 */
	public function getLastBatchNumber()
	{
		$q = $this->db->createQueryBuilder();

		$result = $q->select('MAX(batch) as max')->from($this->table)->execute()->fetch();

		return $result['max'];
	}

	/**
	 * Log that a migration was run.
	 *
	 * @param  string  $file
	 * @param  int     $batch
	 * @return void
	 */
	public function log($file, $batch)
	{
		$record = ['migration' => '\'' . $file . '\'', 'batch' => $batch];

		$q = $this->db->createQueryBuilder();

		$q->insert('migrations')->values($record)->execute();
	}

	/**
	 * Get the last migration batch.
	 *
	 * @return array
	 */
	public function getLast()
	{
		$q = $this->db->createQueryBuilder();

		$result = $q->select('*')->from($this->table)->where('batch = ?')->setParameter(0, $this->getLastBatchNumber())->execute()->fetchAll();

		return array_column($result, 'migration');
	}

	/**
	 * Remove a migration from the log.
	 *
	 * @param  object  $migration
	 * @return void
	 */
	public function delete($migration)
	{
		$q = $this->db->createQueryBuilder();

		$q->delete($this->table)->where('migration = ?')->setparameter(0, $migration)->execute();
	}

	/**
	 * Apply a migration SQL query.
	 *
	 * @param  string  $migration
	 * @return void
	 */
	public function migrate($migration)
	{
		$this->db->prepare($migration)->execute();
	}

	public function createMigrationTable()
	{
		$sql = <<<SQL
CREATE TABLE `migrations` (
  `migration` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SQL;
		$this->migrate($sql);
	}


}
