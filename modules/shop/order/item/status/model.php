<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Order_Item_Status_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Order_Item_Status_Model extends Core_Entity
{
	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'shop_order_item_status' => array('foreign_key' => 'parent_id'),
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop_order_status' => array('foreign_key' => 'parent_id'),
		'shop' => array(),
		'user' => array()
	);

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'shop_order_item_statuses.sorting' => 'ASC',
		'shop_order_item_statuses.name' => 'ASC',
	);

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'sorting' => 0,
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
	 * Backend callback method
	 * @return string
	 */
	public function nameBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$link = $oAdmin_Form_Field->link;
		$onclick = $oAdmin_Form_Field->onclick;

		$link = $oAdmin_Form_Controller->doReplaces($oAdmin_Form_Field, $this, $link);
		$onclick = $oAdmin_Form_Controller->doReplaces($oAdmin_Form_Field, $this, $onclick);

		$return = '<i class="fa ' . ($this->canceled ? 'fa-times-circle' : 'fa-circle') . '" style="margin-right: 5px; color: ' . ($this->color ? htmlspecialchars($this->color) : '#aebec4') . '"></i> '
			. '<a href="' . $link . '" onclick="' . $onclick . '">' . htmlspecialchars($this->name) . '</a>';

		$count = $this->getChildCount();
		$count
			&& $return .= '<span class="badge badge-hostcms badge-square margin-left-5">' . $count . '</span>';

		return $return;
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function shop_order_status_idBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		ob_start();

		$path = $oAdmin_Form_Controller->getPath();

		$oCore_Html_Entity_Dropdownlist = new Core_Html_Entity_Dropdownlist();

		$additionalParams = Core_Str::escapeJavascriptVariable(
			str_replace(array('"'), array('&quot;'), $oAdmin_Form_Controller->additionalParams)
		);

		Core_Html_Entity::factory('Span')
			->class('padding-left-10')
			->add(
				$oCore_Html_Entity_Dropdownlist
					->value($this->shop_order_status_id)
					->options(Shop_Order_Status_Controller_Edit::getDropdownlistOptions($this->shop_id))
					->onchange("$.adminLoad({path: '{$path}', additionalParams: '{$additionalParams}', action: 'apply', post: { 'hostcms[checked][0][{$this->id}]': 0, apply_check_0_{$this->id}_fv_{$oAdmin_Form_Field->id}: $(this).find('li[selected]').prop('id') }, windowId: '{$oAdmin_Form_Controller->getWindowId()}'});")
					->data('change-context', 'true')
				)
			->execute();

		return ob_get_clean();
	}

	/**
	 * Get parent status
	 * @return Shop_Order_Item_Status_Model|NULL
	 */
	public function getParent()
	{
		return $this->parent_id
			? Core_Entity::factory('Shop_Order_Item_Status', $this->parent_id)
			: NULL;
	}

	/**
	 * Get count of items all levels
	 * @return int
	 */
	public function getChildCount()
	{
		$count = $this->Shop_Order_Item_Statuses->getCount();

		$aShop_Order_Item_Statuses = $this->Shop_Order_Item_Statuses->findAll(FALSE);
		foreach ($aShop_Order_Item_Statuses as $oShop_Order_Item_Status)
		{
			$count += $oShop_Order_Item_Status->getChildCount();
		}

		return $count;
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event shop_order_status.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->Shop_Order_Item_Statuses->deleteAll(FALSE);

		Core_QueryBuilder::update('shop_order_items')
			->set('shop_order_item_status_id', 0)
			->where('shop_order_item_status_id', '=', $this->id)
			->execute();

		return parent::delete($primaryKey);
	}
}