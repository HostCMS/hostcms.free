<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Producer_Dir_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2016 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Producer_Dir_Model extends Core_Entity
{
	/**
	 * Backend property
	 * @var int
	 */
	public $img=0;

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'shop_producer' => array(),
		'shop_producer_dir' => array('foreign_key' => 'parent_id')
	);
	
	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop_producer_dir' => array('foreign_key' => 'parent_id'),
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

		if (is_null($id))
		{
			$oUserCurrent = Core_Entity::factory('User', 0)->getCurrent();
			$this->_preloadValues['user_id'] = is_null($oUserCurrent) ? 0 : $oUserCurrent->id;
		}
	}
	
	/**
	 * Get parent
	 * @return Shop_Producer_Dir|NULL
	 */
	public function getParent()
	{
		return $this->parent_id
			? Core_Entity::factory('Shop_Producer_Dir', $this->parent_id)
			: NULL;
	}
}