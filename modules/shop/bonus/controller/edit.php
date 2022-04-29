<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Bonus Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Bonus_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
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
			$object->shop_id = Core_Array::getGet('shop_id');
		}

		$modelName = $object->getModelName();

		switch ($modelName)
		{
			case 'shop_bonus':
				if (!$object->id)
				{
					$object->shop_bonus_dir_id = Core_Array::getGet('shop_bonus_dir_id', 0);
				}
			break;
			case 'shop_bonus_dir':
				if (!$object->id)
				{
					$object->parent_id = Core_Array::getGet('shop_bonus_dir_id', 0);
				}
			break;
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

		$modelName = $this->_object->getModelName();

		switch ($modelName)
		{
			case 'shop_bonus':
				$oMainTab
					->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'));

				// Удаляем группу
				$oAdditionalTab->delete($this->getField('shop_bonus_dir_id'));

				$this->getField('description')
					->rows(7)
					->wysiwyg(Core::moduleIsActive('wysiwyg'));

				$oMainTab->move($this->getField('description')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow2);
				$oMainTab->move($this->getField('start_datetime')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-4 col-lg-3')), $oMainRow3);
				$oMainTab->move($this->getField('end_datetime')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-4 col-lg-3')), $oMainRow3);
				$oMainTab->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-4 col-lg-3')), $oMainRow3);

				$oGroupSelect = Admin_Form_Entity::factory('Select');

				$oGroupSelect
					->caption(Core::_('Shop_Bonus.shop_bonus_dir_id'))
					->options(array(' … ') + self::fillShopBonusDir($this->_object->shop_id))
					->name('shop_bonus_dir_id')
					->value($this->_object->shop_bonus_dir_id)
					->divAttr(array('class' => 'form-group col-xs-12 col-lg-3'));

				$oMainRow3->add($oGroupSelect);

				$oMainTab->move($this->getField('active')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow4);

				$oMainTab->delete($this->getField('type'));
				$oMainTab->delete($this->getField('value'));

				$oMainRow1->add(Admin_Form_Entity::factory('Div')
					->class('col-xs-12 col-sm-6 col-md-4 col-lg-2 input-group select-group')
					->add(Admin_Form_Entity::factory('Code')
						->html('<div class="caption">' . Core::_('Shop_Bonus.value') . '</div>')
					)
					->add(Admin_Form_Entity::factory('Input')
						->name('value')
						->value($this->_object->value)
						->divAttr(array('class' => ''))
						->class('form-control semi-bold')
						->add(Core_Html_Entity::factory('Select')
							->name('type')
							->options(array(
								'%',
								$this->_object->Shop->Shop_Currency->sign
							))
							->value($this->_object->type)
							->class('form-control input-group-addon')
						)
					)
				);

				$oMainTab->move($this->getField('min_amount')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-4 col-lg-2')), $oMainRow1);

				$windowId = $this->_Admin_Form_Controller->getWindowId();

				$accrualValue = $this->_object->accrual_date != '0000-00-00 00:00:00' ? 0 : 1;

				$oShopBonusAccrual = Admin_Form_Entity::factory('Radiogroup')
					->name('accrual')
					->id('accrual' . time())
					->caption(Core::_('Shop_Bonus.accrual'))
					->value($accrualValue)
					->divAttr(array('class' => 'pull-left'))
					->radio(array(
						0 => Core::_('Shop_Bonus.from'),
						1 => Core::_('Shop_Bonus.through')
					))
					->ico(
						array(
							0 => 'fa-calendar',
							1 => 'fa-arrows-h',
					))
					->colors(
						array(
							0 => 'btn-sky',
							1 => 'btn-pink'
						)
					)
					->onchange("radiogroupOnChange('{$windowId}', $(this).val(), [0,1])");

				$oMainRow1->add($oShopBonusDiv = Admin_Form_Entity::factory('Div')
					->class('form-group col-xs-12 col-sm-12 col-md-12 col-lg-8')
					->add($oShopBonusAccrual)
				);

				$oMainTab
					->move($this->getField('accrual_date')->divAttr(array('class' => 'pull-left margin-left-10 hidden-1'))->size(15), $oShopBonusDiv)
					->move($this->getField('accrual_days')->divAttr(array('class' => 'pull-left margin-left-10 hidden-0'))->size(6), $oShopBonusDiv)
					->move($this->getField('expire_days')->divAttr(array('class' => 'pull-left margin-left-10'))->size(6), $oShopBonusDiv);


				$title = $this->_object->id
					? Core::_('Shop_Bonus.edit_title', $this->_object->name)
					: Core::_('Shop_Bonus.add_title');

				$oMainTab->add(
					Admin_Form_Entity::factory('Code')
						->html("<script>radiogroupOnChange('{$windowId}', '{$accrualValue}', [0,1])</script>")
				);
			break;
			case 'shop_bonus_dir':
				$oMainTab
					->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'));

				$this->getField('description')->rows(9)->wysiwyg(Core::moduleIsActive('wysiwyg'));
				$oMainTab->move($this->getField('description')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow2);

				// Удаляем группу
				$oAdditionalTab->delete($this->getField('parent_id'));

				$oGroupSelect = Admin_Form_Entity::factory('Select');

				$oGroupSelect
					->caption(Core::_('Shop_Bonus_Dir.parent_id'))
					->options(array(' … ') + self::fillShopBonusDir($this->_object->shop_id, 0, array($this->_object->id)))
					->name('parent_id')
					->value($this->_object->parent_id)
					->divAttr(array('class' => 'form-group col-xs-12 col-md-6'));

				$oMainRow3->add($oGroupSelect);

				$oMainTab->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow3);

				$title = $this->_object->id
					? Core::_('Shop_Bonus_Dir.edit_title', $this->_object->name)
					: Core::_('Shop_Bonus_Dir.add_title');
			break;
		}

		$this->title($title);

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Shop_Bonus_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		$modelName = $this->_object->getModelName();

		switch ($modelName)
		{
			case 'shop_bonus':
				Core_Array::get($this->_formValues, 'accrual')
					? $this->_formValues['accrual_date'] = ''
					: $this->_formValues['accrual_days'] = 0;
			break;
		}

		parent::_applyObjectProperty();

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));

		return $this;
	}

	/**
	 * Redirect groups tree
	 * @var array
	 */
	static protected $_aGroupTree = array();

	/**
	 * Build visual representation of group tree
	 * @param int $iShopId shop ID
	 * @param int $iShopBonusDirParentId parent ID
	 * @param int $aExclude exclude group ID
	 * @param int $iLevel current nesting level
	 * @return array
	 */
	static public function fillShopBonusDir($iShopId, $iShopBonusDirParentId = 0, $aExclude = array(), $iLevel = 0)
	{
		$iShopId = intval($iShopId);
		$iShopBonusDirParentId = intval($iShopBonusDirParentId);
		$iLevel = intval($iLevel);

		if ($iLevel == 0)
		{
			$aTmp = Core_QueryBuilder::select('id', 'parent_id', 'name')
				->from('shop_bonus_dirs')
				->where('shop_id', '=', $iShopId)
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

		if (isset(self::$_aGroupTree[$iShopBonusDirParentId]))
		{
			$countExclude = count($aExclude);
			foreach (self::$_aGroupTree[$iShopBonusDirParentId] as $childrenGroup)
			{
				if ($countExclude == 0 || !in_array($childrenGroup['id'], $aExclude))
				{
					$aReturn[$childrenGroup['id']] = str_repeat('  ', $iLevel) . $childrenGroup['name'] . ' [' . $childrenGroup['id'] . ']' ;
					$aReturn += self::fillShopBonusDir($iShopId, $childrenGroup['id'], $aExclude, $iLevel + 1);
				}
			}
		}

		$iLevel == 0 && self::$_aGroupTree = array();

		return $aReturn;
	}
}