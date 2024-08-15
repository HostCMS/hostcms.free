<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Item_Controller_Apply_Discount
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Shop_Item_Controller_Apply_Discount extends Admin_Form_Action_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'title',
		'Shop',
		'buttonName',
		'skipColumns'
	);

	/**
	 * Constructor.
	 * @param Admin_Form_Action_Model $oAdmin_Form_Action action
	 */
	public function __construct(Admin_Form_Action_Model $oAdmin_Form_Action)
	{
		parent::__construct($oAdmin_Form_Action);

		$this->buttonName(Core::_('Admin_Form.apply'));
	}

	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return self
	 */
	public function execute($operation = NULL)
	{
		if (is_null($operation))
		{
			// Original windowId
			$windowId = $this->_Admin_Form_Controller->getWindowId();

			$newWindowId = 'Apply_Discount_' . time();

			$oCore_Html_Entity_Form = Core_Html_Entity::factory('Form');

			$oCore_Html_Entity_Div = Core_Html_Entity::factory('Div')
				->id($newWindowId)
				->add($oCore_Html_Entity_Form);

			$oCore_Html_Entity_Form
				->action($this->_Admin_Form_Controller->getPath())
				->method('post');

			$window_Admin_Form_Controller = clone $this->_Admin_Form_Controller;

			// Select на всплывающем окне должен быть найден через ID нового окна, а не id_content
			$window_Admin_Form_Controller->window($newWindowId);

			// Получение списка скидок
			$aDiscounts = array(" … ");
			$aShop_Discounts = $this->Shop->Shop_Discounts->findAll(FALSE);
			foreach ($aShop_Discounts as $oShop_Discount)
			{
				$aDiscounts[$oShop_Discount->id] = $oShop_Discount->getOptions();
			}

			$oAdmin_Form_Entity_Select_Discount = Admin_Form_Entity::factory('Select')
				->name('discount_id')
				->id('discountId')
				->style('width: 280px; float: left')
				->filter(TRUE)
				->options($aDiscounts)
				->caption(Core::_('Shop_Item.discount_select_caption'))
				->controller($window_Admin_Form_Controller);

			$oAdmin_Form_Entity_Select_Discount_Modifications_Checkbox = Admin_Form_Entity::factory('Checkbox')
				->name('flag_include_modifications')
				->caption(Core::_('Shop_Item.flag_include_modifications'));

			$oAdmin_Form_Entity_Select_Discount_Checkbox = Admin_Form_Entity::factory('Checkbox')
				->name('flag_delete_discount')
				->caption(Core::_('Shop_Item.flag_delete_discount'))
				->class('form-control colored-danger times');

			$oCore_Html_Entity_Form
				->add($oAdmin_Form_Entity_Select_Discount)
				->add($oAdmin_Form_Entity_Select_Discount_Modifications_Checkbox)
				->add($oAdmin_Form_Entity_Select_Discount_Checkbox);

			if (Core::moduleIsActive('siteuser'))
			{
				$aBonuses = array(" … ");
				$aShop_Bonuses = $this->Shop->Shop_Bonuses->findAll(FALSE);
				foreach ($aShop_Bonuses as $oShop_Bonus)
				{
					$aBonuses[$oShop_Bonus->id] = $oShop_Bonus->getOptions();
				}

				$oAdmin_Form_Entity_Select_Bonus = Admin_Form_Entity::factory('Select')
					->name('bonus_id')
					->id('bonusId')
					->style('width: 280px; float: left')
					->filter(TRUE)
					->options($aBonuses)
					->caption(Core::_('Shop_Item.bonus_select_caption'))
					->controller($window_Admin_Form_Controller);

				$oAdmin_Form_Entity_Select_Bounus_Modifications_Checkbox = Admin_Form_Entity::factory('Checkbox')
					->name('flag_bonus_include_modifications')
					->caption(Core::_('Shop_Item.flag_bonus_include_modifications'));

				$oAdmin_Form_Entity_Select_Bonus_Checkbox = Admin_Form_Entity::factory('Checkbox')
					->name('flag_delete_bonus')
					->caption(Core::_('Shop_Item.flag_delete_bonus'))
					->class('form-control colored-danger times');

				$oCore_Html_Entity_Form
					->add($oAdmin_Form_Entity_Select_Bonus)
					->add($oAdmin_Form_Entity_Select_Bounus_Modifications_Checkbox)
					->add($oAdmin_Form_Entity_Select_Bonus_Checkbox);
			}

			$oCore_Html_Entity_Form->add(
				Admin_Form_Entity::factory('Select')
					->name('shop_producer_id')
					->caption(Core::_('Shop_Item.shop_producer_id'))
					->options(Shop_Item_Controller_Edit::fillProducersList($this->Shop->id))
					->controller($window_Admin_Form_Controller)
					->filter(TRUE)
			);

			// Идентификаторы переносимых указываем скрытыми полями в форме, чтобы не превысить лимит GET
			$aChecked = $this->_Admin_Form_Controller->getChecked();

			// Clear checked list
			$this->_Admin_Form_Controller->clearChecked();

			foreach ($aChecked as $datasetKey => $checkedItems)
			{
				$oAdmin_Form_Dataset_Entity = $this->_Admin_Form_Controller->getDataset($datasetKey);

				if ($oAdmin_Form_Dataset_Entity && get_class($oAdmin_Form_Dataset_Entity->getEntity()) == 'Shop_Item_Model')
				{
					foreach ($checkedItems as $key => $value)
					{
						$oCore_Html_Entity_Form->add(
							 Core_Html_Entity::factory('Input')
								->name('hostcms[checked][' . $datasetKey . '][' . $key . ']')
								->value(1)
								->type('hidden')
						);
					}
				}
			}

			$oAdmin_Form_Entity_Button = Admin_Form_Entity::factory('Button')
				->name('apply')
				->type('submit')
				->class('applyButton btn btn-blue')
				->value($this->buttonName)
				->onclick(
					//'$("#' . $newWindowId . '").parents(".modal").remove(); '
					'bootbox.hideAll(); '
					. $this->_Admin_Form_Controller->getAdminSendForm(array('operation' => 'apply'))
				)
				->controller($this->_Admin_Form_Controller);

			$oCore_Html_Entity_Form
				->add(
					Admin_Form_Entity::factory('Div')
						->class('form-group col-xs-12')
						->add($oAdmin_Form_Entity_Button)
				);

			$oCore_Html_Entity_Div->execute();

			ob_start();

			Core_Html_Entity::factory('Script')
				->value("$(function() {
					$('#{$newWindowId}').HostCMSWindow({ autoOpen: true, destroyOnClose: false, title: '" . Core_Str::escapeJavascriptVariable($this->title) . "', AppendTo: '#{$windowId}', width: 750, height: 400, addContentPadding: true, modal: false, Maximize: false, Minimize: false }); });")
				->execute();

			$this->addMessage(ob_get_clean());

			// Break execution for other
			return TRUE;
		}
		else
		{
			$iDiscountID = Core_Array::getPost('discount_id', 0, 'int');
			$iBonusID = Core_Array::getPost('bonus_id', 0, 'int');

			$oShop_Item = $this->_object;

			if ($iDiscountID)
			{
				$this->_applyDiscounts($oShop_Item, $iDiscountID);
			}

			if (Core::moduleIsActive('siteuser') && $iBonusID)
			{
				$this->_applyBonuses($oShop_Item, $iBonusID);
			}

			$this->_clear($oShop_Item);
		}

		return $this;
	}

	/**
	 * Clear shop item
	 * @param Shop_Item_Model $oShop_Item
	 * @return self
	 */
	protected function _clear(Shop_Item_Model $oShop_Item)
	{
		$oShop_Item->clearCache();

		// Fast filter
		if ($this->Shop->filter)
		{
			$oShop_Filter_Controller = new Shop_Filter_Controller($this->Shop);
			$oShop_Filter_Controller->fill($oShop_Item);
		}

		return $this;
	}

	/**
	 * Get shop items
	 * @param Shop_Item_Model $oShop_Item
	 * @param int $flag_include_modifications
	 * @return array
	 */
	protected function _getShopItems(Shop_Item_Model $oShop_Item, $flag_include_modifications = 0)
	{
		$aReturn = array();

		$shop_producer_id = Core_Array::getPost('shop_producer_id', 0 , 'int');

		if ($shop_producer_id)
		{
			if ($oShop_Item->shop_producer_id == $shop_producer_id)
			{
				$aReturn[] = $oShop_Item;
			}

			if ($flag_include_modifications)
			{
				$aModifications = $oShop_Item->Modifications->findAll(FALSE);
				foreach ($aModifications as $oModification)
				{
					if ($oModification->shop_producer_id == $shop_producer_id)
					{
						$aReturn[] = $oModification;
					}
				}
			}
		}
		else
		{
			$aReturn[] = $oShop_Item;

			if ($flag_include_modifications)
			{
				$aModifications = $oShop_Item->Modifications->findAll(FALSE);
				foreach ($aModifications as $oModification)
				{
					$aReturn[] = $oModification;
				}
			}
		}

		return $aReturn;
	}

	/**
	 * Apply discounts
	 * @param Shop_Item_Model $oShop_Item
	 * @param int $iDiscountID
	 * @return self
	 */
	protected function _applyDiscounts(Shop_Item_Model $oShop_Item, $iDiscountID)
	{
		$oShop_Discount = Core_Entity::factory('Shop_Discount', $iDiscountID);

		$aObjects = $this->_getShopItems($oShop_Item, !is_null(Core_Array::getPost('flag_include_modifications')));

		foreach ($aObjects as $oShop_Item)
		{
			if (!is_null(Core_Array::getPost('flag_delete_discount')))
			{
				$oShop_Item->remove($oShop_Discount);
			}
			else
			{
				// Устанавливаем скидку товару
				is_null($oShop_Item->Shop_Item_Discounts->getByShop_discount_id($iDiscountID))
					&& $oShop_Item->add($oShop_Discount);
			}

			$this->_clear($oShop_Item);
		}

		return $this;
	}

	/**
	 * Apply bonuses
	 * @param Shop_Item_Model $oShop_Item
	 * @param int $iBonusID
	 * @return self
	 */
	protected function _applyBonuses(Shop_Item_Model $oShop_Item, $iBonusID)
	{
		$oShop_Bonus = Core_Entity::factory('Shop_Bonus', $iBonusID);

		$aBonusObjects = $this->_getShopItems($oShop_Item, !is_null(Core_Array::getPost('flag_bonus_include_modifications')));

		foreach ($aBonusObjects as $oShop_Item)
		{
			if (!is_null(Core_Array::getPost('flag_delete_bonus')))
			{
				$oShop_Item->remove($oShop_Bonus);
			}
			else
			{
				// Устанавливаем бонус товару
				$oShop_Item->add($oShop_Bonus)
					&& is_null($oShop_Item->Shop_Item_Bonuses->getByShop_bonus_id($iBonusID));
			}

			$this->_clear($oShop_Item);
		}

		return $this;
	}
}