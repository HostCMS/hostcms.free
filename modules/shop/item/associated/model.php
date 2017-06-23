<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Item_Associated_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2016 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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

		if (is_null($id))
		{
			$oUserCurrent = Core_Entity::factory('User', 0)->getCurrent();
			$this->_preloadValues['user_id'] = is_null($oUserCurrent) ? 0 : $oUserCurrent->id;
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
}