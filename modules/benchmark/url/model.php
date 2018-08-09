<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Benchmark_Url_Model
 *
 * @package HostCMS
 * @subpackage Benchmark
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Benchmark_Url_Model extends Core_Entity
{
	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'benchmark_url';

	/**
	 * Time, average
	 * @var mixed
	 */
	public $time_avr = 0;

	/**
	 * Date, average
	 * @var mixed
	 */
	public $date_avr = 0;

	/**
	 * Load DOM, average
	 * @var mixed
	 */
	public $waiting_time_avr = 0;

	/**
	 * URL name
	 * @var mixed
	 */
	public $name = '';

	/**
	 * Load page, average
	 * @var mixed
	 */
	public $load_page_time_avr = 0;

	/**
	 * DNS lookup, average
	 * @var mixed
	 */
	public $dns_lookup_avr = 0;

	/**
	 * Connect to server, average
	 * @var mixed
	 */
	public $connect_server_avr = 0;

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'structure' => array()
	);

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
			$this->_preloadValues['ip'] = Core_Array::get($_SERVER, 'REMOTE_ADDR');
		}
	}
}