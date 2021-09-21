<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Item_Associated_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Item_Associated_Model extends Core_Entity
{
	/**
	 * Name of the table
	 * @var string
	 */
	protected $_tableName = 'shop_item_associated';

	/**
	 * Name of the model
	 * @var string
	 */
	protected $_modelName = 'shop_item_associated';

	/**
	 * Disable markDeleted()
	 * @var mixed
	 */
	protected $_marksDeleted = NULL;

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop_item' => array(),
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
		}
	}

	/**
	 * Get item by associated id
	 * @param int $iAssociatedId id
	 * @return Shop_Item_Associated_Model|NULL
	 */
	public function getByAssociatedId($iAssociatedId)
	{
		$this->queryBuilder()
		//->clear()
		->where('shop_item_associated_id', '=', $iAssociatedId)
		->limit(1);

		$aObjects = $this->findAll();

		if (count($aObjects) > 0)
		{
			return $aObjects[0];
		}

		return NULL;
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event shop_item_associated.onBeforeGetRelatedSite
	 * @hostcms-event shop_item_associated.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Shop_Item->Shop->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}