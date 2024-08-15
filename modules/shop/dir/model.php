<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Dir_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Shop_Dir_Model extends Core_Entity
{
	/**
	 * Backend property
	 * @var string
	 */
	public $img = 0;

	/**
	 * Backend property
	 * @var string
	 */
	public $email = '';

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'shop' => array(),
		'shop_dir' => array('foreign_key' => 'parent_id')
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'site' => array(),
		'shop_dir' => array('foreign_key' => 'parent_id'),
		'user' => array()
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
			$oUser = Core_Auth::getCurrentUser();
			$this->_preloadValues['user_id'] = is_null($oUser) ? 0 : $oUser->id;
			$this->_preloadValues['site_id'] = defined('CURRENT_SITE') ? CURRENT_SITE : 0;
		}
	}

	/**
	 * Copy object
	 * @return Core_Entity
	 * @hostcms-event shop_dir.onAfterRedeclaredCopy
	 */
	public function copy()
	{
		$newObject = parent::copy();

		$aShop_Dirs = $this->Shop_Dirs->findAll();
		foreach ($aShop_Dirs as $oShop_Dir)
		{
			$newObject->add($oShop_Dir->copy());
		}

		$aShops = $this->Shops->findAll();
		foreach ($aShops as $oShop)
		{
			$newObject->add($oShop->copy());
		}

		Core_Event::notify($this->_modelName . '.onAfterRedeclaredCopy', $newObject, array($this));

		return $newObject;
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return self
	 * @hostcms-event shop_dir.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->Shops->deleteAll(FALSE);
		$this->Shop_Dirs->deleteAll(FALSE);

		return parent::delete($primaryKey);
	}

	/**
	 * Get parent shop dir
	 * @return Shop_Dir_Model|NULL
	 */
	public function getParent()
	{
		return $this->parent_id
			? Core_Entity::factory('Shop_Dir', $this->parent_id)
			: NULL;
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event shop_dir.onBeforeGetRelatedSite
	 * @hostcms-event shop_dir.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}