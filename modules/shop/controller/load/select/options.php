<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 *
 * Контроллер загрузки значений списка товаров для select доп. св-в
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Shop_Controller_Load_Select_Options extends Admin_Form_Action_Controller_Type_Load_Select_Options
{
	/**
	 * Get Shop_Item option name
	 * @param Shop_Item_Model $oShop_Item
	 * @return string
	 * @hostcms-event Shop_Controller_Load_Select_Options.onGetOptionName
	 */
	static public function getOptionName(Shop_Item_Model $oShop_Item)
	{
		Core_Event::notify('Shop_Controller_Load_Select_Options.onGetOptionName', $oShop_Item);

		$eventResult = Core_Event::getLastReturn();

		if (!is_null($eventResult))
		{
			return $eventResult;
		}

		return ($oShop_Item->modification_id ? ' — ' : '') . $oShop_Item->name . ($oShop_Item->marking != '' ? " ({$oShop_Item->marking})" : '');
	}

	/**
	 * Add value
	 * @return self
	 */
	public function addValues()
	{
		$aConfig = Core_Config::instance()->get('property_config', array()) + array(
			'select_modifications' => TRUE,
		);

		foreach ($this->_objects as $Object)
		{
			$oTmp = new stdClass();
			$oTmp->value = $Object->id;
			$oTmp->name = self::getOptionName(!$Object->shortcut_id ? $Object : $Object->Shop_Item);

			if (!$Object->active)
			{
				$oTmp->attr = array('class' => 'darkgray line-through');
			}

			$this->_values[] = $oTmp;

			// Shop Item's modifications
			if ($aConfig['select_modifications'])
			{
				$oModifications = $Object->Modifications;

				$oModifications
					->queryBuilder()
					->clearOrderBy()
					->clearSelect()
					->select('id', 'shortcut_id', 'modification_id', 'name', 'marking', 'active');

				$aModifications = $oModifications->findAll(FALSE);

				foreach ($aModifications as $oModification)
				{
					$oTmp = new stdClass();
					$oTmp->value = $oModification->id;
					$oTmp->name = self::getOptionName($oModification);

					if (!$oModification->active)
					{
						$oTmp->attr = array('class' => 'darkgray line-through');
					}

					$this->_values[] = $oTmp;
				}
			}
		}

		return $this;
	}

	/**
	 * Get count of objects
	 * @return self
	 */
	protected function _getCount()
	{
		return $this->_model->getCount();
	}

	/**
	 * Find objects by $this->_model
	 * @return self
	 */
	protected function _findObjects()
	{
		$this->_objects = Property_Controller_Tab::getShopItems($this->_model);

		return $this;
	}
}