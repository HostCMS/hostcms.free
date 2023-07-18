<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Warehouse_Cell Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Warehouse_Cell_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
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
			$object->shop_warehouse_id = Core_Array::getGet('shop_warehouse_id', 0);
			$object->parent_id = Core_Array::getGet('parent_id', 0);
		}

		return parent::setObject($object);
	}

	/**
	 * Prepare backend item's edit form
	 *
	 * @return self
	 */
	protected function _prepareForm()
	{
		parent::_prepareForm();

		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
		;

		$oMainTab
			->move($this->getField('name')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow1)
			->move($this->getField('description')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow2);

		$oAdditionalTab->delete($this->getField('parent_id'));

		$oShop_Warehouse_Cells = Core_Entity::factory('Shop_Warehouse_Cell');
		$oShop_Warehouse_Cells->queryBuilder()
			->where('shop_warehouse_id', '=', $this->_object->shop_warehouse_id);

		$iCountCells = $oShop_Warehouse_Cells->getCount();

		if ($iCountCells < Core::$mainConfig['switchSelectToAutocomplete'])
		{
			// Селектор с родительским макетом
			$oSelect_Cells = Admin_Form_Entity::factory('Select');
			$oSelect_Cells
				->options(
					array(' … ') + self::fillCellParent(0, $this->_object->id)
				)
				->name('parent_id')
				->value($this->_object->parent_id)
				->caption(Core::_('Shop_Warehouse_Cell.parent_id'))
				->divAttr(array('class' => 'form-group col-xs-12 col-md-3'));

			$oMainRow3->add($oSelect_Cells);
		}
		else
		{
			$oCellInput = Admin_Form_Entity::factory('Input')
				->caption(Core::_('Shop_Warehouse_Cell.parent_id'))
				->divAttr(array('class' => 'form-group col-xs-12 col-md-3'))
				->name('parent_name');

			if ($this->_object->parent_id)
			{
				$oShop_Warehouse_Cell = Core_Entity::factory('Shop_Warehouse_Cell', $this->_object->parent_id);
				$oCellInput->value($oShop_Warehouse_Cell->name . ' [' . $oShop_Warehouse_Cell->id . ']');
			}

			$oCellInputHidden = Admin_Form_Entity::factory('Input')
				->divAttr(array('class' => 'form-group col-xs-12 hidden'))
				->name('parent_id')
				->value($this->_object->parent_id)
				->type('hidden');

			$oCore_Html_Entity_Script = Core_Html_Entity::factory('Script')
			->value("
				$('#{$windowId} [name = parent_name]').autocomplete({
					source: function(request, response) {
						$.ajax({
							url: '/admin/shop/warehouse/cell/index.php?autocomplete=1&show_parents=1&shop_warehouse_id={$this->_object->shop_warehouse_id}',
							dataType: 'json',
							data: {
								queryString: request.term
							},
							success: function(data) {
								response(data);
							}
						});
					},
					minLength: 1,
					create: function() {
						$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
							return $('<li></li>')
								.data('item.autocomplete', item)
								.append($('<a>').text(item.label))
								.appendTo(ul);
						}
						$(this).prev('.ui-helper-hidden-accessible').remove();
					},
					select: function(event, ui) {
						$('#{$windowId} [name = parent_id]').val(ui.item.id);
					},
					open: function() {
						$(this).removeClass('ui-corner-all').addClass('ui-corner-top');
					},
					close: function() {
						$(this).removeClass('ui-corner-top').addClass('ui-corner-all');
					}
				});
			");

			$oMainRow3
				->add($oCellInput)
				->add($oCellInputHidden)
				->add($oCore_Html_Entity_Script);
		}

		$this->title($this->_object->id
			? Core::_('Shop_Warehouse_Cell.form_edit', $this->_object->name, FALSE)
			: Core::_('Shop_Warehouse_Cell.form_add')
		);

		return $this;
	}

	/**
	 * Create visual tree of the directories
	 * @param int $iCellParentId parent cell ID
	 * @param boolean $bExclude exclude cell ID
	 * @param int $iLevel current nesting level
	 * @return array
	 */
	static public function fillCellParent($iCellParentId = 0, $bExclude = FALSE, $iLevel = 0)
	{
		$iCellParentId = intval($iCellParentId);
		$iLevel = intval($iLevel);

		$oShop_Warehouse_Cell_Parent = Core_Entity::factory('Shop_Warehouse_Cell', $iCellParentId);

		$aReturn = array();

		// Дочерние элементы
		$childrenCells = $oShop_Warehouse_Cell_Parent->Shop_Warehouse_Cells;
		$childrenCells->queryBuilder()
			->where('shop_warehouse_id', '=', Core_Array::getGet('shop_warehouse_id', 0));

		$childrenCells = $childrenCells->findAll();

		if (count($childrenCells))
		{
			foreach ($childrenCells as $childrenCell)
			{
				if ($bExclude != $childrenCell->id)
				{
					$aReturn[$childrenCell->id] = str_repeat('  ', $iLevel) . $childrenCell->name;
					$aReturn += self::fillCellParent($childrenCell->id, $bExclude, $iLevel + 1);
				}
			}
		}

		return $aReturn;
	}
}