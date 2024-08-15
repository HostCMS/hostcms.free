<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Seo_Site_Model
 *
 * @package HostCMS
 * @subpackage Seo
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Seo_Site_Model extends Core_Entity
{
	/**
	 * Name of the model
	 * @var string
	 */
	protected $_modelName = 'seo_site';

	/**
	 * Column consist item's name
	 * @var string
	 */
	protected $_nameColumn = 'site_id';

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

		if (is_null($id) && !$this->loaded())
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

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return self
	 * @hostcms-event seo_site.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->Seo_Ratings->deleteAll(FALSE);
		$this->Seo_Indexeds->deleteAll(FALSE);
		$this->Seo_Links->deleteAll(FALSE);
		$this->Seo_Pages->deleteAll(FALSE);
		$this->Seo_Queries->deleteAll(FALSE);

		return parent::delete($primaryKey);
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event seo_site.onBeforeGetRelatedSite
	 * @hostcms-event seo_site.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}