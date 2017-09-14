<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Benchmark.
 *
 * @package HostCMS
 * @subpackage Benchmark
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк"(Hostmake LLC), http://www.hostcms.ru
 */
class Benchmark_Controller
{
	/**
	 * The singleton instances.
	 * @var mixed
	 */
	static public $instance = NULL;

	/**
	 * DataBase instance
	 * @var string
	 */
	protected $_database = NULL;

	/**
	 * Temporary directory
	 * @var int
	 */
	protected $_temporary_directory = "";

	/**
	 * Config array
	 * @var array
	 */
	static public $aConfig = NULL;

	/**
	 * Register an existing instance as a singleton.
	 * @return object
	 */
	static public function instance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		self::getConfig();

		$this->_database = Core_Database::instance();

		$this->_temporary_directory = CMS_FOLDER . TMP_DIR;
	}

	public static function getConfig()
	{
		if (is_null(self::$aConfig))
		{
			self::$aConfig = Core_Config::instance()->get('benchmark_config', array()) + array(
				'database_table_name' => 'performance_test',
				'database_write_query_count' => 10000,
				'database_read_query_count' => 10000,
				'database_change_query_count' => 10000,
				'sample_text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
				'files_count' => 1000,
				'math_count' => 100000,
				'string_count' => 1000,
				'benchmark_file_path' => 'http://www.hostcms.ru/download/benchmark/1mb',
			);
		}

		return self::$aConfig;
	}

	/*
	 * Clears test table
	 * @return self
	 */
	public function clearTable()
	{
		$sTableName = $this->_database->quoteColumnName(self::$aConfig['database_table_name']);

		$this->_database->query("TRUNCATE TABLE {$sTableName}");

		return $this;
	}

	/*
	 * Creates test table
	 * @return self
	 */
	public function createTable()
	{
		$sTableName = $this->_database->quoteColumnName(self::$aConfig['database_table_name']);

		$this->_database->query("DROP TABLE IF EXISTS {$sTableName}");

		$aConfig = $this->_database->getConfig();

		$this->_database->query(
			"CREATE TABLE IF NOT EXISTS {$sTableName} (
			`id` int(11) NOT NULL auto_increment,
			`value` varchar(255) NOT NULL,
			PRIMARY KEY (`id`)
		) ENGINE={$aConfig['storageEngine']} DEFAULT CHARSET=utf8");

		return $this;
	}

	/*
	 * Drop test table
	 * @return self
	 */
	public function dropTable()
	{
		$sTableName = $this->_database->quoteColumnName(self::$aConfig['database_table_name']);

		$this->_database->query("DROP TABLE IF EXISTS {$sTableName}");

		return $this;
	}

	/*
	 * Write in DB table test, returns number of records per second
	 * @return int
	 */
	public function writeTable()
	{
		$this->clearTable();

		$sTableName = $this->_database->quoteColumnName(self::$aConfig['database_table_name']);

		$startTime = Core::getmicrotime();

		for ($i = 0; $i < self::$aConfig['database_write_query_count']; $i++)
		{
			$this->_database->query("INSERT INTO {$sTableName} (`value`) VALUES ({$i})");
		}

		$fQueryTime = Core::getmicrotime() - $startTime;

		return $fQueryTime > 0
			? abs(round(self::$aConfig['database_write_query_count'] / $fQueryTime))
			: 0;
	}

	/*
	 * Read from DB table test, returns number of readed records per second
	 * @return int
	 */
	public function readTable()
	{
		$sTableName = $this->_database->quoteColumnName(self::$aConfig['database_table_name']);

		$startTime = Core::getmicrotime();

		for ($i = 0; $i < self::$aConfig['database_read_query_count']; $i++)
		{
			$iID = rand(1, self::$aConfig['database_read_query_count']);
			$this->_database->query("SELECT * FROM {$sTableName} WHERE `id` = {$iID}");
		}

		$fQueryTime = Core::getmicrotime() - $startTime;

		return $fQueryTime > 0
			? abs(round(self::$aConfig['database_read_query_count'] / $fQueryTime))
			: 0;
	}

	/*
	 * Change DB table test, returns number of changed records per second
	 * @return int
	 */
	public function changeTable()
	{
		$sTableName = $this->_database->quoteColumnName(self::$aConfig['database_table_name']);

		$startTime = Core::getmicrotime();

		for ($i = 0; $i < self::$aConfig['database_change_query_count']; $i++)
		{
			$iID = rand(1, self::$aConfig['database_change_query_count']);
			$this->_database->query("UPDATE {$sTableName} SET `value` = `value` * {$iID} WHERE `id`={$iID}");
		}

		$fQueryTime = Core::getmicrotime() - $startTime;

		return $fQueryTime > 0
			? abs(round(self::$aConfig['database_change_query_count'] / $fQueryTime))
			: 0;
	}

	/*
	 * Counts file system operations per second
	 * @return int
	 */
	public function fileSystemTest()
	{
		$startTime = Core::getmicrotime();

		for ($i = 0; $i < self::$aConfig['files_count']; $i++)
		{
			$sFileName = $this->_temporary_directory . "test{$i}.tmp";

			$rFile = fopen($sFileName, "w");

			fwrite($rFile, self::$aConfig['sample_text']);

			fclose($rFile);

			unlink($sFileName);
		}

		$fQueryTime = (Core::getmicrotime() - $startTime) / 3;

		return $fQueryTime > 0
			? abs(round(self::$aConfig['files_count'] / $fQueryTime))
			: 0;
	}

	/*
	 * Counts number of mathematical functions per second
	 * @return int
	 */
	public function cpuMathTest()
	{
		$startTime = Core::getmicrotime();

		for ($i = 0; $i < self::$aConfig['math_count']; $i++)
		{
			$rad = deg2rad($i);
			sin($rad);
			cos($rad);
			acos($rad);
			asin($rad);
			atan($rad);
			tan($rad);
		}

		$fQueryTime=(Core::getmicrotime() - $startTime) / 7;

		return $fQueryTime > 0
			? abs(round(self::$aConfig['math_count'] / $fQueryTime))
			: 0;
	}

	/*
	 * Counts number of string functions per second
	 * @return int
	 */
	public function cpuStringTest()
	{
		$startTime = Core::getmicrotime();

		for ($i = 0; $i < self::$aConfig['string_count']; $i++)
		{
			strtoupper(self::$aConfig['sample_text']);
			strtolower(self::$aConfig['sample_text']);
			strrev(self::$aConfig['sample_text']);
			strlen(self::$aConfig['sample_text']);
			md5(self::$aConfig['sample_text']);
			sha1(self::$aConfig['sample_text']);
		}

		$fQueryTime = (Core::getmicrotime() - $startTime) / 6;

		return $fQueryTime > 0
			? abs(round(self::$aConfig['string_count'] / $fQueryTime))
			: 0;
	}

	/*
	 * Download speed test, returns megabit/sec.
	 * @return float
	 */
	public function networkDownloadTest()
	{
		$startTime = Core::getmicrotime();

		$sFileContent = file_get_contents(self::$aConfig['benchmark_file_path']);

		$fQueryTime = Core::getmicrotime() - $startTime;

		$iFileLen = strlen($sFileContent);

		return $fQueryTime > 0
			? abs(round((($iFileLen * 8) / 1024 / 1024) / $fQueryTime, 2))
			: 0;
	}

	/*
	 * Email speed test
	 * @return float
	 */
	public function mailTest()
	{
		$oSite = Core_Entity::factory('Site', CURRENT_SITE);

		$startTime = Core::getmicrotime();

		@mail($oSite->admin_email, 'Performance test', self::$aConfig['sample_text']);

		return abs(round(Core::getmicrotime() - $startTime, 4));
	}

	/*
	 * Delete old urls
	 * @return self
	 */
	public function deleteOldUrlBenchmarks()
	{
		// Получаем дату, начиная с которой необходимо хранить урлы
		$date_storage = Core_Date::timestamp2sql(strtotime("-1 month"));

		// Удаляем старые данные из таблицы статистики
		Core_DataBase::instance()->setQueryType(3)
			->query("DELETE LOW_PRIORITY QUICK FROM `benchmark_urls` WHERE `datetime` < '{$date_storage}' LIMIT 5000");

		return $this;
	}

	/**
	 * Get DataBase Storage Engines
	 * @return array
	 */
	static public function getStorageEngines()
	{
		$aResult = Core_DataBase::instance()->setQueryType(9)
			->query("SHOW ENGINES")
			->asAssoc()
			->result();

		return $aResult;
	}

	/**
	 * Get list of tables
	 * @return array
	 */
	static public function getTables()
	{
		$aResult = Core_DataBase::instance()->setQueryType(9)
			->query("SHOW TABLE STATUS")
			->asAssoc()
			->result();

		return $aResult;
	}
}