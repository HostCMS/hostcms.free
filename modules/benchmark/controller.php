<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Benchmark.
 *
 * @package HostCMS
 * @subpackage Benchmark
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк"(Hostmake LLC), http://www.hostcms.ru
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
	 * Table name
	 * @var string
	 */
	protected $_table_name = NULL;

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

		$this->_table_name = $this->_database->quoteColumnName(self::$aConfig['database_table_name']);
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
		$this->_database->query("TRUNCATE TABLE {$this->_table_name}");

		return $this;
	}

	/*
	 * Creates test table
	 * @return self
	 */
	public function createTable()
	{
		$this->_database->query("DROP TABLE IF EXISTS {$this->_table_name}");

		$aConfig = $this->_database->getConfig();

		$this->_database->query(
			"CREATE TABLE IF NOT EXISTS {$this->_table_name} (
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
		$this->_database->query("DROP TABLE IF EXISTS {$this->_table_name}");

		return $this;
	}

	/*
	 * Write in DB table test, returns number of records per second
	 * @return int
	 */
	public function writeTable()
	{
		$this->clearTable();

		$startTime = Core::getmicrotime();

		for ($i = 0; $i < self::$aConfig['database_write_query_count']; $i++)
		{
			$this->_database->query("INSERT INTO {$this->_table_name} (`value`) VALUES ({$i})");
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
		$startTime = Core::getmicrotime();

		for ($i = 0; $i < self::$aConfig['database_read_query_count']; $i++)
		{
			$iID = rand(1, self::$aConfig['database_read_query_count']);
			$this->_database->query("SELECT * FROM {$this->_table_name} WHERE `id` = {$iID}");
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
		$startTime = Core::getmicrotime();

		for ($i = 0; $i < self::$aConfig['database_change_query_count']; $i++)
		{
			$iID = rand(1, self::$aConfig['database_change_query_count']);
			$this->_database->query("UPDATE {$this->_table_name} SET `value` = `value` * {$iID} WHERE `id` = {$iID}");
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
		if (ini_get('allow_url_fopen'))
		{
			$startTime = Core::getmicrotime();
			
			$sFileContent = file_get_contents(self::$aConfig['benchmark_file_path']);

			$fQueryTime = Core::getmicrotime() - $startTime;

			$iFileLen = strlen($sFileContent);

			return $fQueryTime > 0
				? abs(round((($iFileLen * 8) / 1024 / 1024) / $fQueryTime, 2))
				: 0;
		}
		
		return FALSE;
	}

	/*
	 * Email speed test
	 * @return float
	 */
	public function mailTest()
	{
		$oSite = Core_Entity::factory('Site', CURRENT_SITE);

		$startTime = Core::getmicrotime();

		@mail($oSite->getFirstEmail(), 'Performance test', self::$aConfig['sample_text']);

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
	
	/**
	 * Show Benchmark JS-code
	 */
	static public function show()
	{
		if (defined('BENCHMARK_ENABLE') && BENCHMARK_ENABLE)
		{
?><!-- HostCMS Benchmark --><script>
window.addEventListener('load', function() {
	var waiting = performance.timing.responseStart - performance.timing.requestStart, loadPage = performance.timing.loadEventStart - performance.timing.requestStart, dnsLookup = performance.timing.domainLookupEnd - performance.timing.domainLookupStart, connectServer = performance.timing.connectEnd - performance.timing.connectStart;

	xmlhttprequest = new XMLHttpRequest();
	xmlhttprequest.open('POST','/hostcms-benchmark.php',true);
	xmlhttprequest.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	xmlhttprequest.send('structure_id=<?php echo CURRENT_STRUCTURE_ID?>&waiting_time='+waiting+'&load_page_time='+loadPage+'&dns_lookup='+dnsLookup+'&connect_server='+connectServer);
});
</script><?php
		}
	}
}