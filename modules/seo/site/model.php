<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Seo_Site_Model
 *
 * @package HostCMS
 * @subpackage Seo
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Seo_Site_Model extends Core_Entity
{
	/**
	 * Name of the model
	 * @var string
	 */
	protected $_modelName = 'seo_site';

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'site' => array(),
		'seo_driver' => array()
	);

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'seo_rating' => array(),
		'seo_indexed' => array(),
		'seo_link' => array(),
		'seo_page' => array(),
		'seo_query' => array()
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
			$this->_preloadValues['site_id'] = defined('CURRENT_SITE') ? CURRENT_SITE : 0;
		}
	}

	/**
	 * Change seo status
	 * @return self
	 * @hostcms-event seo_site.onBeforeChangeActive
	 * @hostcms-event seo_site.onAfterChangeActive
	 */
	public function changeActive()
	{
		Core_Event::notify($this->_modelName . '.onBeforeChangeActive', $this);

		$this->active = 1 - $this->active;
		$this->save();

		Core_Event::notify($this->_modelName . '.onAfterChangeActive', $this);

		return $this;
	}

	/**
	 * Backend
	 */
	public function driver()
	{
		$oSeo_Driver = Core_Entity::factory('Seo_Driver', $this->seo_driver_id);

		if (!is_null($oSeo_Driver))
		{
			return htmlspecialchars($oSeo_Driver->name);
		}
	}
}