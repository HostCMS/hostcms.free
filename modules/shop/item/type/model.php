<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Item_Type_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Shop_Item_Type_Model extends Core_Entity
{
	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'shop_item_type';

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'user' => array(),
		'account_chartaccount' => array('model' => 'Chartaccount', 'foreign_key' => 'account'),
		'income_account_chartaccount' => array('model' => 'Chartaccount', 'foreign_key' => 'income_account'),
		'expenditure_account_chartaccount' => array('model' => 'Chartaccount', 'foreign_key' => 'expenditure_account')
	);

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'shop_item' => array()
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
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function accountBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		if (Core::moduleIsActive('chartaccount'))
		{
			return $this->account
				? htmlspecialchars($this->Account_Chartaccount->code) . ' ' . htmlspecialchars($this->Account_Chartaccount->name)
				: '';
		}

		return '';
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event shop_item_type.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		Core_QueryBuilder::update('shop_items')
			->set('shop_item_type_id', 0)
			->where('shop_item_type_id', '=', $this->id)
			->execute();

		return parent::delete($primaryKey);
	}
}