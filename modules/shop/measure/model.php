<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Measure_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Measure_Model extends Core_Entity
{
	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'shop' => array()
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'user' => array()
	);

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'shop_measures.name' => 'ASC'
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
	 * Merge shop measure
	 * @param Shop_Measure_Model $oObject
	 * @return self
	 */
	public function merge(Shop_Measure_Model $oObject)
	{
		trim($this->name) == ''
			&& $oObject->name != ''
			&& $this->name = $oObject->name;

		trim($this->description) == ''
			&& $oObject->description != ''
			&& $this->description = $oObject->description;

		$this->okei == 0
			&& $oObject->okei
			&& $this->okei = $oObject->okei;

		$this->save();

		Core_QueryBuilder::update('shop_items')
			->set('shop_measure_id', $this->id)
			->where('shop_measure_id', '=', $oObject->id)
			->execute();

		Core_QueryBuilder::update('shop_order_items')
			->set('shop_measure_id', $this->id)
			->where('shop_measure_id', '=', $oObject->id)
			->execute();

		$oObject->markDeleted();

		return $this;
	}
}