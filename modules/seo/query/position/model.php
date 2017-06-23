<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Seo_Query_Position_Model
 *
 * @package HostCMS
 * @subpackage Seo
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2016 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Seo_Query_Position_Model extends Core_Entity
{
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
		'seo_query' => array(),
		'user' => array()
	);

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'yandex' => 0,
		'google' => 0,
		'yahoo' => 0,
		'bing' => 0,
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
			$this->_preloadValues['datetime'] = Core_Date::timestamp2sql(time());
		}
	}

	/**
	 * Get Seo_Query_Positions by date
	 * @param datetime $start_datetime start date
	 * @param datetime $end_datetime end date
	 * @return array
	 */
	public function getByDatetime($start_datetime, $end_datetime)
	{
		$this->queryBuilder()
			// ->clear()
			->where('datetime', '>', $start_datetime)
			->where('datetime', '<', $end_datetime)
			->orderBy('datetime');

		return $this->findAll();
	}
}