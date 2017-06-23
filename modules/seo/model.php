<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Seo_Model
 *
 * @package HostCMS
 * @subpackage Seo
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Seo_Model extends Core_Entity
{
	/**
	 * Column consist item's name
	 * @var string
	 */
	protected $_nameColumn = 'datetime';

	/**
	 * Name of the table
	 * @var string
	 */
	protected $_tableName = 'seo';

	/**
	 * Name of the model
	 * @var string
	 */
	protected $_modelName = 'seo';

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'site' => array(),
		'user' => array()
	);

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'tcy' => 0,
		'yandex_indexed' => 0,
		'yahoo_indexed' => 0,
		'bing_indexed' => 0,
		'google_indexed' => 0,
		'google_links' => 0,
		'yandex_links' => 0,
		'yahoo_links' => 0,
		'bing_links' => 0,
	);

	/**
	 * Constructor.
	 * @param int $id entity ID
	 */
	public function __construct($id = NULL)
	{
		parent::__construct($id);

		if (is_null($id))
		{
			$oUserCurrent = Core_Entity::factory('User', 0)->getCurrent();
			$this->_preloadValues['user_id'] = is_null($oUserCurrent) ? 0 : $oUserCurrent->id;
			$this->_preloadValues['site_id'] = defined('CURRENT_SITE') ? CURRENT_SITE : 0;
			$this->_preloadValues['datetime'] = Core_Date::timestamp2sql(time());
		}
	}

	/**
	 * Get SEO data by date
	 * @param datetime $start_datetime start date
	 * @param datetime $end_datetime end date
	 * @return array
	 */
	public function getByDatetime($start_datetime, $end_datetime)
	{
		$this->queryBuilder()
			//->clear()
			->where('datetime', '>', $start_datetime)
			->where('datetime', '<', $end_datetime)
			->orderBy('datetime');

		return $this->findAll();
	}
}