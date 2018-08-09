<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Producer_Dir_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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

	/**
	 * Move dir to another
	 * @param int $shop_producer_dir_id dir id
	 * @return self
	 */
	public function move($shop_producer_dir_id)
	{
		$oDestinationDir = Core_Entity::factory('Shop_Producer_Dir', $shop_producer_dir_id);

		do
		{
			if ($oDestinationDir->parent_id == $this->id
				|| $oDestinationDir->id == $this->id)
			{
				// Группа назначения является потомком текущей группы, перенос невозможен
				return $this;
			}
		} while ($oDestinationDir = $oDestinationDir->getParent());

		$this->parent_id = $shop_producer_dir_id;
		$this->save();
		return $this;
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function nameBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$count = $this->getChildCount();

		$count > 0 && Core::factory('Core_Html_Entity_Span')
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
		$count = $this->Shop_Producers->getCount();

		$aShop_Producer_Dirs = $this->Shop_Producer_Dirs->findAll(FALSE);
		foreach ($aShop_Producer_Dirs as $oShop_Producer_Dir)
		{
			$count += $oShop_Producer_Dir->getChildCount();
		}

		return $count;
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

		$this->Shop_Producer_Dirs->deleteAll(FALSE);
		$this->Shop_Producers->deleteAll(FALSE);

		return parent::delete($primaryKey);
	}
}