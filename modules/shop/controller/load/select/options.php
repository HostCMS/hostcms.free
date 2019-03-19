<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 *
 * Контроллер загрузки значений списка товаров для select доп. св-в
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Controller_Load_Select_Options extends Admin_Form_Action_Controller_Type_Load_Select_Options
{
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
			$oTmp->name = !$Object->shortcut_id
				? $Object->name
				: $Object->Shop_Item->name;

			$this->_values[] = $oTmp;

			// Shop Item's modifications
			if ($aConfig['select_modifications'])
			{
				$oModifications = $Object->Modifications;

				$oModifications
					->queryBuilder()
					->clearOrderBy()
					->clearSelect()
					->select('id', 'shortcut_id', 'name');

				$aModifications = $oModifications->findAll(FALSE);

				foreach ($aModifications as $oModification)
				{
					$oTmp = new stdClass();
					$oTmp->value = $oModification->id;
					$oTmp->name = ' — ' . $oModification->name;
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