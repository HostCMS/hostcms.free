<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Order_Status Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Order_Status_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		if (!$object->id)
		{
			$object->parent_id = Core_Array::getGet('parent_id', 0);
		}

		parent::setObject($object);

		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'));

		$oAdditionalTab->delete($this->getField('parent_id'));

		$oSelect_Statuses = Admin_Form_Entity::factory('Select');
		$oSelect_Statuses
			->options(
				array(' … ') + self::getSelectOptions(0, $this->_object->id)
			)
			->name('parent_id')
			->value($this->_object->parent_id)
			->caption(Core::_('Shop_Order_Status.parent_id'))
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-3'));

		$oMainRow2->add($oSelect_Statuses);

		$sColorValue = ($this->_object->id && $this->getField('color')->value)
			? $this->getField('color')->value
			: '#aebec4';

		$this->getField('color')
			->class('form-control colorpicker minicolors-input')
			->value($sColorValue);


		$oScript = Admin_Form_Entity::factory('Script')
			->value("$('.colorpicker').each(function () {
				$(this).minicolors({
					control: $(this).attr('data-control') || 'hue',
					defaultValue: $(this).attr('data-defaultValue') || '',
					inline: $(this).attr('data-inline') === 'true',
					letterCase: $(this).attr('data-letterCase') || 'lowercase',
					opacity: $(this).attr('data-opacity'),
					position: $(this).attr('data-position') || 'bottom left',
					change: function (hex, opacity) {
						if (!hex) return;
						if (opacity) hex += ', ' + opacity;
					},
					theme: 'bootstrap'
				});
			});"
		);

		$oMainTab
			->move($this->getField('name')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow1)
			->move($this->getField('color')->set('data-control', 'hue')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow2)
			->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow2)
			->move($this->getField('description')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow3)
			->add($oScript);

		$title = $this->_object->id
			? Core::_('Shop_Order_Status.order_status_edit_form_title', $this->_object->name)
			: Core::_('Shop_Order_Status.order_status_add_form_title');

		$this->title($title);

		return $this;
	}

	/**
	 * Array of Shop_Order_Statuses tree
	 * @var NULL|array
	 */
	static protected $_statusesTree = NULL;

	static protected function _getStatusesTree($parent_id)
	{
		if (is_null(self::$_statusesTree))
		{
			self::$_statusesTree = array();

			$oShop_Order_Statuses = Core_Entity::factory('Shop_Order_Status')->findAll(FALSE);
			foreach ($oShop_Order_Statuses as $oShop_Order_Status)
			{
				self::$_statusesTree[$oShop_Order_Status->parent_id][] = $oShop_Order_Status;
			}
		}

		return isset(self::$_statusesTree[$parent_id])
			? self::$_statusesTree[$parent_id]
			: array();
	}

	/**
	 * Create visual tree of the statuses
	 * @param int $iParentId parent cell ID
	 * @param boolean $bExclude exclude cell ID
	 * @param int $iLevel current nesting level
	 * @return array
	 */
	static public function getSelectOptions($iParentId = 0, $bExclude = FALSE, $iLevel = 0)
	{
		$iLevel = intval($iLevel);

		$aReturn = array();

		// Дочерние элементы
		$aShop_Order_Statuses = self::_getStatusesTree($iParentId);
		foreach ($aShop_Order_Statuses as $childrenType)
		{
			if ($bExclude != $childrenType->id)
			{
				$aReturn[$childrenType->id] = str_repeat('  ', $iLevel) . $childrenType->name;
				$aReturn += self::getSelectOptions($childrenType->id, $bExclude, $iLevel + 1);
			}
		}

		return $aReturn;
	}

	/**
	 * Create visual tree of the statuses for dropdownlist
	 * @param int $iParentId parent cell ID
	 * @param int $iLevel current nesting level
	 * @return array
	 */
	static public function getDropdownlistOptions($iParentId = 0, $iLevel = 0)
	{
		$iLevel = intval($iLevel);

		$aReturn = array(array('value' => Core::_('Shop_Order.notStatus'), 'color' => '#aebec4'));

		$oShop_Order_Status_Parent = Core_Entity::factory('Shop_Order_Status', $iParentId);

		// Дочерние элементы
		$aShop_Order_Statuses = self::_getStatusesTree($iParentId);

		foreach ($aShop_Order_Statuses as $childrenStatus)
		{
			$aReturn[$childrenStatus->id] = array(
				'value' => $childrenStatus->name,
				'color' => $childrenStatus->color,
				'level' => $iLevel
			);

			$aReturn += self::getDropdownlistOptions($childrenStatus->id, $iLevel + 1);
		}

		return $aReturn;
	}
}