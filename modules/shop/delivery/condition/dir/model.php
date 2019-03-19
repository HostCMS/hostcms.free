<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Delivery_Condition_Dir_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Delivery_Condition_Dir_Model extends Core_Entity
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
		'shop_delivery_condition_dir' => array('foreign_key' => 'parent_id'),
		'shop_delivery_condition' => array(),
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop_delivery_condition_dir' => array('foreign_key' => 'parent_id'),
		'shop_delivery' => array(),
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
	 * Get parent
	 * @return self|NULL
	 */
	public function getParent()
	{
		return $this->parent_id
			? Core_Entity::factory('Shop_Delivery_Condition_Dir', $this->parent_id)
			: NULL;
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function nameBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$link = $oAdmin_Form_Field->link;
		$onclick = $oAdmin_Form_Field->onclick;

		$link = $oAdmin_Form_Controller->doReplaces($oAdmin_Form_Field, $this, $link);
		$onclick = $oAdmin_Form_Controller->doReplaces($oAdmin_Form_Field, $this, $onclick);

		$oCore_Html_Entity_Div = Core::factory('Core_Html_Entity_Div');

		$oCore_Html_Entity_Div
			->add(
				Core::factory('Core_Html_Entity_A')
					->href($link)
					->onclick($onclick)
					->value(htmlspecialchars($this->name))
			);

		$count = $this->getChildCount();
		$count && $oCore_Html_Entity_Div
			->add(
				Core::factory('Core_Html_Entity_Span')
					->class('badge badge-hostcms badge-square')
					->value($count)
			);

		$oCore_Html_Entity_Div->execute();
	}

	/**
	 * Get count
	 * @return int
	 */
	public function getChildCount()
	{
		$count = $this->Shop_Delivery_Conditions->getCount();

		$aShop_Delivery_Condition_Dirs = $this->Shop_Delivery_Condition_Dirs->findAll(FALSE);

		foreach ($aShop_Delivery_Condition_Dirs as $oShop_Delivery_Condition_Dir)
		{
			$count += $oShop_Delivery_Condition_Dir->getChildCount();
		}

		return $count;
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return self
	 * @hostcms-event shop_delivery_condition_dir.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->Shop_Delivery_Conditions->deleteAll(FALSE);
		$this->Shop_Delivery_Condition_Dirs->deleteAll(FALSE);

		return parent::delete($primaryKey);
	}
}