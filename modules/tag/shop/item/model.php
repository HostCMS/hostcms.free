<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Tag_Shop_Item_Model
 *
 * @package HostCMS
 * @subpackage Tag
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2016 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Tag_Shop_Item_Model extends Core_Entity
{
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
		'tag' => array(),
		'shop_item' => array(),
		'site' => array()
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
	 * Get teg by ID of shop item 
	 * @param int $shop_item_id item ID
	 * @return mixed
	 */
	public function getByShopItem($shop_item_id)
	{
		$this->queryBuilder()
			//->clear()
			->where('shop_item_id', '=', $shop_item_id)
			->limit(1);

		$aTag_Shop_Items = $this->findAll();

		if (isset($aTag_Shop_Items[0]))
		{
			return $aTag_Shop_Items[0];
		}

		return NULL;
	}
}