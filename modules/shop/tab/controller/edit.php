<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Tab Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Tab_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		$object->shop_id = Core_Array::getGet('shop_id');

		$modelName = $object->getModelName();

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		switch ($modelName)
		{
			case 'shop_tab':
				if (!$object->id)
				{
					$object->shop_tab_dir_id = Core_Array::getGet('shop_tab_dir_id', 0);
				}

				parent::setObject($object);

				$title = $this->_object->id
					? Core::_('Shop_Tab.edit_title', $this->_object->name)
					: Core::_('Shop_Tab.add_title');

				$oMainTab = $this->getTab('main');
				$oAdditionalTab = $this->getTab('additional');

				$oMainTab
					->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow5 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow6 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow7 = Admin_Form_Entity::factory('Div')->class('row'))
					;

				$sColorValue = ($this->_object->id && $this->getField('color')->value)
					? $this->getField('color')->value
					: '#aebec4';

				$this->getField('color')
					->colorpicker(TRUE)
					->value($sColorValue);

				$oMainTab
					->move($this->getField('name')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow1)
					->move($this->getField('caption')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow2)
					->move($this->getField('color')->set('data-control', 'hue')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow2)
					->move($this->getField('icon')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow2)
					->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow2);

				// Удаляем группу
				$oAdditionalTab->delete($this->getField('shop_tab_dir_id'));

				$oGroupSelect = Admin_Form_Entity::factory('Select');

				$oGroupSelect
					->caption(Core::_('Shop_Tab.shop_tab_dir_id'))
					->options(array(' … ') + self::fillShopTabDir())
					->name('shop_tab_dir_id')
					->value($this->_object->shop_tab_dir_id)
					->divAttr(array('class' => 'form-group col-xs-12 col-md-6'));

				$oMainRow3->add($oGroupSelect);

				$oMainTab->delete($this->getField('text'));

				$oText = Admin_Form_Entity::factory('Textarea')
					->value($this->_object->text)
					->rows(15)
					->caption(Core::_('Shop_Tab.text'))
					->name('text')
					->wysiwyg(Core::moduleIsActive('wysiwyg'))
					->divAttr(array('class' => 'form-group col-xs-12'))
					->template_id(0);

				$oMainRow4->add($oText);

				$oAdditionalGroupsSelect = Admin_Form_Entity::factory('Select')
					->caption(Core::_('Shop_Tab.shop_tab_group'))
					->options($this->_fillShopTabGroups())
					->name('shop_tab_group_id[]')
					->class('shop-tab-groups')
					->style('width: 100%')
					->multiple('multiple')
					->divAttr(array('class' => 'form-group col-xs-12'));

				$this->addField($oAdditionalGroupsSelect);
				$oMainRow5->add($oAdditionalGroupsSelect);

				$html = '
					<script>
						$(function(){
							$("#' . $windowId . ' .shop-tab-groups").select2({
								language: "' . Core_I18n::instance()->getLng() . '",
								minimumInputLength: 1,
								placeholder: "' . Core::_('Shop_Item.select_group') . '",
								tags: true,
								allowClear: true,
								multiple: true,
								ajax: {
									url: "/admin/shop/item/index.php?shortcuts&includeRoot=1&shop_id=' . $this->_object->shop_id .'",
									dataType: "json",
									type: "GET",
									processResults: function (data) {
										var aResults = [];
										$.each(data, function (index, item) {
											aResults.push({
												"id": item.id,
												"text": item.text
											});
										});
										return {
											results: aResults
										};
									}
								},
							});
						})</script>
					';

				$oMainRow5->add(Admin_Form_Entity::factory('Code')->html($html));

				$oAdditionalItemsSelect = Admin_Form_Entity::factory('Select')
					->caption(Core::_('Shop_Tab.shop_tab_item'))
					->options($this->_fillShopTabItems())
					->name('shop_tab_item_id[]')
					->class('shop-tab-items')
					->style('width: 100%')
					->multiple('multiple')
					->divAttr(array('class' => 'form-group col-xs-12'));

				$this->addField($oAdditionalItemsSelect);
				$oMainRow6->add($oAdditionalItemsSelect);

				$html2 = '
					<script>
						$(function(){
							$("#' . $windowId . ' .shop-tab-items").select2({
								language: "' . Core_I18n::instance()->getLng() . '",
								minimumInputLength: 1,
								placeholder: "' . Core::_('Shop_Tab.select_item') . '",
								tags: true,
								allowClear: true,
								multiple: true,
								ajax: {
									url: "/admin/shop/item/index.php?items&shop_id=' . $this->_object->shop_id .'",
									dataType: "json",
									type: "GET",
									processResults: function (data) {
										var aResults = [];
										$.each(data, function (index, item) {
											aResults.push({
												"id": item.id,
												"text": item.text
											});
										});
										return {
											results: aResults
										};
									}
								},
							});
						})</script>
					';

				$oMainRow6->add(Admin_Form_Entity::factory('Code')->html($html2));

				$oAdditionalProducersSelect = Admin_Form_Entity::factory('Select')
					->caption(Core::_('Shop_Tab.shop_tab_producer'))
					->options($this->_fillShopTabProducers())
					->name('shop_tab_producer_id[]')
					->class('shop-tab-producers')
					->style('width: 100%')
					->multiple('multiple')
					->divAttr(array('class' => 'form-group col-xs-12'));

				$this->addField($oAdditionalProducersSelect);
				$oMainRow7->add($oAdditionalProducersSelect);

				$html3 = '
					<script>
						$(function(){
							$("#' . $windowId . ' .shop-tab-producers").select2({
								language: "' . Core_I18n::instance()->getLng() . '",
								minimumInputLength: 1,
								placeholder: "' . Core::_('Shop_Tab.select_producer') . '",
								tags: true,
								allowClear: true,
								multiple: true,
								ajax: {
									url: "/admin/shop/item/index.php?producers&shop_id=' . $this->_object->shop_id .'",
									dataType: "json",
									type: "GET",
									processResults: function (data) {
										var aResults = [];
										$.each(data, function (index, item) {
											aResults.push({
												"id": item.id,
												"text": item.text
											});
										});
										return {
											results: aResults
										};
									}
								},
							});
						})</script>
					';

				$oMainRow7->add(Admin_Form_Entity::factory('Code')->html($html3));

			break;

			case 'shop_tab_dir':
				if (!$object->id)
				{
					$object->parent_id = Core_Array::getGet('shop_tab_dir_id');
				}

				parent::setObject($object);

				$title = $this->_object->id
					? Core::_('Shop_Tab_Dir.dir_edit_form_title', $this->_object->name)
					: Core::_('Shop_Tab_Dir.dir_add_form_title');

				// Получаем стандартные вкладки
				$oMainTab = $this->getTab('main');
				$oAdditionalTab = $this->getTab('additional');

				$oMainTab
					->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
					;

				$oMainTab
					->move($this->getField('name')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow1);

				// Удаляем группу
				$oAdditionalTab->delete($this->getField('parent_id'));

				$oGroupSelect = Admin_Form_Entity::factory('Select')
					->caption(Core::_('Shop_Tab_Dir.parent_id'))
					->options(array(' … ') + self::fillShopTabDir(0, array($this->_object->id)))
					->name('parent_id')
					->value($this->_object->parent_id)
					->divAttr(array('class' => 'form-group col-xs-12 col-md-6'));

				$oMainRow2->add($oGroupSelect);

				$oMainTab
					->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-3')), $oMainRow2);
			break;
		}

		$this->title($title);

		return $this;
	}

	/**
	 * Groups tree
	 * @var array
	 */
	static protected $_aGroupTree = array();

	/**
	 * Build visual representation of group tree
	 * @param int $iShopTabDirParentId parent ID
	 * @param int $aExclude exclude group ID
	 * @param int $iLevel current nesting level
	 * @return array
	 */
	static public function fillShopTabDir($iShopTabDirParentId = 0, $aExclude = array(), $iLevel = 0)
	{
		$iShopTabDirParentId = intval($iShopTabDirParentId);
		$iLevel = intval($iLevel);

		if ($iLevel == 0)
		{
			$aTmp = Core_QueryBuilder::select('id', 'parent_id', 'name')
				->from('shop_tab_dirs')
				->where('deleted', '=', 0)
				->orderBy('sorting')
				->orderBy('name')
				->execute()->asAssoc()->result();

			foreach ($aTmp as $aGroup)
			{
				self::$_aGroupTree[$aGroup['parent_id']][] = $aGroup;
			}
		}

		$aReturn = array();

		if (isset(self::$_aGroupTree[$iShopTabDirParentId]))
		{
			$countExclude = count($aExclude);
			foreach (self::$_aGroupTree[$iShopTabDirParentId] as $childrenGroup)
			{
				if ($countExclude == 0 || !in_array($childrenGroup['id'], $aExclude))
				{
					$aReturn[$childrenGroup['id']] = str_repeat('  ', $iLevel) . $childrenGroup['name'] . ' [' . $childrenGroup['id'] . ']' ;
					$aReturn += self::fillShopTabDir($childrenGroup['id'], $aExclude, $iLevel + 1);
				}
			}
		}

		$iLevel == 0 && self::$_aGroupTree = array();

		return $aReturn;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Shop_Tab_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 * @return self
	 */
	protected function _applyObjectProperty()
	{
		$modelName = $this->_object->getModelName();

		// Backup revision
		if (Core::moduleIsActive('revision') && $this->_object->id)
		{
			$modelName == 'shop_tab'
				&& $this->_object->backupRevision();
		}

		parent::_applyObjectProperty();

		switch ($modelName)
		{
			case 'shop_tab':
				// Группы
				$aShopTabGroupIds = Core_Array::getPost('shop_tab_group_id', array());
				!is_array($aShopTabGroupIds) && $aShopTabGroupIds = array();

				$aTmp = array();

				$aShop_Tab_Groups = $this->_object->Shop_Tab_Groups->findAll(FALSE);
				foreach ($aShop_Tab_Groups as $oShop_Tab_Group)
				{
					!in_array($oShop_Tab_Group->shop_group_id, $aShopTabGroupIds)
						? $oShop_Tab_Group->delete()
						: $aTmp[] = $oShop_Tab_Group->shop_group_id;
				}

				// Новые группы
				$aNewShopTabGroupIDs = array_diff($aShopTabGroupIds, $aTmp);
				foreach ($aNewShopTabGroupIDs as $iShopTabGroupId)
				{
					$oShop_Tab_Group = Core_Entity::factory('Shop_Tab_Group');
					$oShop_Tab_Group->shop_id = $this->_object->shop_id;
					$oShop_Tab_Group->shop_group_id = $iShopTabGroupId;
					$oShop_Tab_Group->shop_tab_id = $this->_object->id;
					$oShop_Tab_Group->save();
				}

				// Товары
				$aShopTabItemIds = Core_Array::getPost('shop_tab_item_id', array());
				!is_array($aShopTabItemIds) && $aShopTabItemIds = array();

				$aTmp = array();

				$aShop_Tab_Items = $this->_object->Shop_Tab_Items->findAll(FALSE);
				foreach ($aShop_Tab_Items as $oShop_Tab_Item)
				{
					!in_array($oShop_Tab_Item->shop_item_id, $aShopTabItemIds)
						? $oShop_Tab_Item->delete()
						: $aTmp[] = $oShop_Tab_Item->shop_item_id;
				}

				// Новые товары
				$aNewShopTabItemIDs = array_diff($aShopTabItemIds, $aTmp);
				foreach ($aNewShopTabItemIDs as $iNewShopTabItemId)
				{
					$oShop_Tab_Item = Core_Entity::factory('Shop_Tab_Item');
					$oShop_Tab_Item->shop_id = $this->_object->shop_id;
					$oShop_Tab_Item->shop_item_id = $iNewShopTabItemId;
					$oShop_Tab_Item->shop_tab_id = $this->_object->id;
					$oShop_Tab_Item->save();
				}

				// Производители
				$aShopTabProducerIds = Core_Array::getPost('shop_tab_producer_id', array());
				!is_array($aShopTabProducerIds) && $aShopTabProducerIds = array();

				$aTmp = array();

				$aShop_Tab_Producers = $this->_object->Shop_Tab_Producers->findAll(FALSE);
				foreach ($aShop_Tab_Producers as $oShop_Tab_Producer)
				{
					!in_array($oShop_Tab_Producer->shop_producer_id, $aShopTabProducerIds)
						? $oShop_Tab_Producer->delete()
						: $aTmp[] = $oShop_Tab_Producer->shop_producer_id;
				}

				// Новые производители
				$aNewShopTabProducerIDs = array_diff($aShopTabProducerIds, $aTmp);
				foreach ($aNewShopTabProducerIDs as $iNewShopTabProducerId)
				{
					$oShop_Tab_Producer = Core_Entity::factory('Shop_Tab_Producer');
					$oShop_Tab_Producer->shop_id = $this->_object->shop_id;
					$oShop_Tab_Producer->shop_producer_id = $iNewShopTabProducerId;
					$oShop_Tab_Producer->shop_tab_id = $this->_object->id;
					$oShop_Tab_Producer->save();
				}
			break;
		}

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));

		return $this;
	}

	/**
	 * Fill shortcut groups list
	 * @return array
	 */
	protected function _fillShopTabGroups()
	{
		$aReturn = array();

		$aShop_Tab_Groups = $this->_object->Shop_Tab_Groups->findAll(FALSE);
		foreach ($aShop_Tab_Groups as $oShop_Tab_Group)
		{
			$oShop_Group = $oShop_Tab_Group->Shop_Group;

			$aParentGroups = array();

			$aTmpGroup = $oShop_Group;

			// Добавляем все директории от текущей до родителя.
			do {
				$aParentGroups[] = $aTmpGroup->name;
			} while ($aTmpGroup = $aTmpGroup->getParent());

			$sParents = implode(' → ', array_reverse($aParentGroups));

			if (!is_null($oShop_Group->id))
			{
				$aReturn[$oShop_Group->id] = array(
					'value' => $sParents . ' [' . $oShop_Group->id . ']',
					'attr' => array('selected' => 'selected')
				);
			}
			else
			{
				$aReturn[0] = array(
					'value' => Core::_('Shop_Item.root') . ' [0]',
					'attr' => array('selected' => 'selected')
				);
			}
		}

		return $aReturn;
	}

	/**
	 * Fill shortcut groups list
	 * @return array
	 */
	protected function _fillShopTabItems()
	{
		$aReturn = array();

		$aShop_Tab_Items = $this->_object->Shop_Tab_Items->findAll(FALSE);
		foreach ($aShop_Tab_Items as $oShop_Tab_Item)
		{
			$oShop_Item = $oShop_Tab_Item->Shop_Item;

			if (!is_null($oShop_Item->id))
			{
				$aReturn[$oShop_Item->id] = array(
					'value' => $oShop_Item->name . ' [' . $oShop_Item->id . ']',
					'attr' => array('selected' => 'selected')
				);
			}
		}

		return $aReturn;
	}

	/**
	 * Fill shortcut groups list
	 * @return array
	 */
	protected function _fillShopTabProducers()
	{
		$aReturn = array();

		$aShop_Tab_Producers = $this->_object->Shop_Tab_Producers->findAll(FALSE);
		foreach ($aShop_Tab_Producers as $oShop_Tab_Producer)
		{
			$oShop_Producer = $oShop_Tab_Producer->Shop_Producer;

			if (!is_null($oShop_Producer->id))
			{
				$aReturn[$oShop_Producer->id] = array(
					'value' => $oShop_Producer->name . ' [' . $oShop_Producer->id . ']',
					'attr' => array('selected' => 'selected')
				);
			}
		}

		return $aReturn;
	}
}