<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Filter_Seo_Dir_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Shop_Filter_Seo_Dir_Model extends Core_Entity
{
	/**
	 * Backend property
	 * @var int
	 */
	public $img = 0;

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'shop_filter_seo' => array(),
		'shop_filter_seo_dir' => array('foreign_key' => 'parent_id')
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop_filter_seo_dir' => array('foreign_key' => 'parent_id'),
		'shop' => array(),
		'user' => array()
	);

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'sorting' => 0
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
		}
	}

	/**
	 * Get parent
	 * @return Shop_Filter_Seo_Dir|NULL
	 */
	public function getParent()
	{
		return $this->parent_id
			? Core_Entity::factory('Shop_Filter_Seo_Dir', $this->parent_id)
			: NULL;
	}

	/**
	 * Backend badge
	 */
	public function nameBadge()
	{
		$count = $this->getChildCount();

		$count > 0 && Core_Html_Entity::factory('Span')
			->class('badge badge-hostcms badge-square')
			->value($count)
			->execute();
	}

	/**
	 * Get count of items all levels
	 * @return int
	 */
	public function getChildCount()
	{
		$count = $this->Shop_Filter_Seos->getCount();

		$aShop_Filter_Seo_Dirs = $this->Shop_Filter_Seo_Dirs->findAll(FALSE);
		foreach ($aShop_Filter_Seo_Dirs as $oShop_Filter_Seo_Dir)
		{
			$count += $oShop_Filter_Seo_Dir->getChildCount();
		}

		return $count;
	}

	/**
	 * Get dir path with separator
	 * @return string
	 */
	public function groupPathWithSeparator($separator = ' → ', $offset = 0)
	{
		$aParentGroups = array();

		$aTmpGroup = $this;

		// Добавляем все директории от текущей до родителя.
		do {
			$aParentGroups[] = $aTmpGroup->name;
		} while ($aTmpGroup = $aTmpGroup->getParent());

		$offset > 0
			&& $aParentGroups = array_slice($aParentGroups, $offset);

		$sParents = implode($separator, array_reverse($aParentGroups));

		return $sParents;
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		$this->Shop_Filter_Seo_Dirs->deleteAll(FALSE);
		$this->Shop_Filter_Seos->deleteAll(FALSE);

		return parent::delete($primaryKey);
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event shop_filter_seo_dir.onBeforeGetRelatedSite
	 * @hostcms-event shop_filter_seo_dir.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Shop->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}