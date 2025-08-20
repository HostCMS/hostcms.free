<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Benchmark_Model
 *
 * @package HostCMS
 * @subpackage Benchmark
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Benchmark_Model extends Core_Entity
{
	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'benchmark';

	/**
	 * Column consist item's name
	 * @var string
	 */
	protected $_nameColumn = 'datetime';

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'site' => array()
	);

	/**
	 * MySQL etalon write
	 * @var mixed
	 */
	public $etalon_mysql_write = 10000;

	/**
	 * MySQL etalon read
	 * @var mixed
	 */
	public $etalon_mysql_read = 10000;

	/**
	 * MySQL etalon update
	 * @var mixed
	 */
	public $etalon_mysql_update = 10000;

	/**
	 * File system etalon, ops
	 * @var mixed
	 */
	public $etalon_filesystem = 15000;

	/**
	 * CPU math etalon
	 * @var mixed
	 */
	public $etalon_cpu_math = 1000000;

	/**
	 * CPU string etalon
	 * @var mixed
	 */
	public $etalon_cpu_string = 1500000;

	/**
	 * Network etalon, Mbps
	 * @var mixed
	 */
	public $etalon_network = 10;

	/**
	 * Mail etalon, sec
	 * @var mixed
	 */
	public $etalon_mail = 0.0500;

	/**
	 * Constructor.
	 * @param int $id entity ID
	 */
	public function __construct($id = NULL)
	{
		parent::__construct($id);

		if (is_null($id) && !$this->loaded())
		{
			$this->_preloadValues['datetime'] = Core_Date::timestamp2sql(time());
		}
	}

	/**
	 * Get coefficient for benchmark
	 * @return self
	 */
	public function getCoefficient($value, $max)
	{
		$iCoefficient = intval($value * 100 / $max);

		return $iCoefficient < 100
			? $iCoefficient
			: 100;
	}

	/**
	 * Get coefficient for mail
	 * @return self
	 */
	public function getMailCoefficient()
	{
		// $iMin = $this->etalon_mail;

		if ($this->mail < $this->etalon_mail)
		{
			return 100;
		}
		elseif ($this->mail > $this->etalon_mail + 1)
		{
			return 0;
		}

		return 100 - ($this->mail - $this->etalon_mail) * 100;
	}

	/**
	 * Get benchmark
	 * @return int
	 */
	public function getBenchmark()
	{
		return ceil(
			$this->getCoefficient($this->mysql_write, $this->etalon_mysql_write) * 0.2 +
			$this->getCoefficient($this->mysql_read, $this->etalon_mysql_read) * 0.2 +
			$this->getCoefficient($this->mysql_update, $this->etalon_mysql_update) * 0.2 +
			$this->getCoefficient($this->filesystem, $this->etalon_filesystem) * 0.1 +
			$this->getCoefficient($this->cpu_math, $this->etalon_cpu_math) * 0.1 +
			$this->getCoefficient($this->cpu_string, $this->etalon_cpu_string) * 0.1 +
			$this->getCoefficient($this->network, $this->etalon_network) * 0.05 +
			$this->getMailCoefficient() * 0.05
		);
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function getBenchmarkBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$iBenchmark = $this->getBenchmark();

		$aColors = Benchmark_Controller::getColors();
		$sColor = $aColors[ceil($iBenchmark / 25)];

		Core_Html_Entity::factory('Span')
			->class($sColor)
			->value($iBenchmark)
			->execute();
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function mysql_writeBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		return Benchmark_Controller::format($this->mysql_write);
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function mysql_readBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		return Benchmark_Controller::format($this->mysql_read);
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function mysql_updateBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		return Benchmark_Controller::format($this->mysql_update);
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function filesystemBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		return Benchmark_Controller::format($this->filesystem);
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function cpu_mathBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		return Benchmark_Controller::format($this->cpu_math);
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function cpu_stringBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		return Benchmark_Controller::format($this->cpu_string);
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event benchmark.onBeforeGetRelatedSite
	 * @hostcms-event benchmark.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}