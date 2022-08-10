<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Company_Location_Controller_Edit
 *
 * @package HostCMS
 * @subpackage Company
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Company_Location_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
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
			$object->company_id = Core_Array::getGet('company_id', 0);
			$object->parent_id = Core_Array::getGet('parent_id', 0);
		}

		parent::setObject($object);

		$this->title($this->_object->id
			? Core::_('Company_Location.edit_title', $this->_object->name)
			: Core::_('Company_Location.add_title'));

		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'));

		$oMainTab
			->move($this->getField('name')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow1)
			->move($this->getField('description')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow2);

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		$oAdditionalTab->delete($this->getField('company_department_id'));

		$oMainRow3->add(
			Admin_Form_Entity::factory('Select')
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-3'))
				->caption(Core::_('Company_Location.company_department_id'))
				->name('company_department_id')
				->id('company_department_id')
				->options(
					array('...')
				)
		);

		$oScript = Admin_Form_Entity::factory('Script')
			->value("$(function() {
				$('#{$windowId} input[name = company_id]').data({'current_value': " . (intval($this->_object->company_id)) . "});
				$('#{$windowId} #company_department_id').data({'current_value': " . (intval($this->_object->company_department_id)) . "});

				$.ajaxRequest({
					path: '/admin/company/department/index.php',
					context: 'company_department_id',
					callBack: [$.loadSelectOptionsCallback, function(data){
						var iCompanyDepartmentId = $('#{$windowId} input[name = company_id]').val() == $('#{$windowId} input[name = company_id]').data('current_value') ? $('#{$windowId} #company_department_id').data('current_value') : 0;
						$('#{$windowId} #company_department_id').val(iCompanyDepartmentId);
					}],
					action: 'loadCompanyDepartments',
					additionalParams: 'company_id=' + $('#{$windowId} input[name = company_id]').val() + '&loadDepartments', windowId: '{$windowId}'
				});
			})");

		$oMainRow3->add($oScript);

		$oAdditionalTab->delete($this->getField('responsible_user_id'));

		// Ответственный сотрудник
		$oSite = Core_Entity::factory('Site', CURRENT_SITE);
		$aSelectResponsibleUsers = $oSite->Companies->getUsersOptions();

		$oSelectResponsibleUsers = Admin_Form_Entity::factory('Select')
			->id('responsible_user_id')
			->options($aSelectResponsibleUsers)
			->name('responsible_user_id')
			->value($this->_object->responsible_user_id)
			->caption(Core::_('Company_Location.responsible_user_id'))
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-3'));

		$oScriptResponsibleUsers = Admin_Form_Entity::factory('Script')
			->value('$(function() {
				$("#' . $windowId . ' #responsible_user_id").selectUser({
					placeholder: "' . Core::_('Company_Location.start_input') . '",
					language: "' . Core_I18n::instance()->getLng() . '",
					dropdownParent: $("#' . $windowId . '")
				})
				.val("' . $this->_object->responsible_user_id . '")
				.trigger("change.select2");
			});');

		$oMainRow3
			->add($oSelectResponsibleUsers)
			->add($oScriptResponsibleUsers);

		$oAdditionalTab->delete($this->getField('parent_id'));

		$oSelect_Types = Admin_Form_Entity::factory('Select');
		$oSelect_Types
			->options(
				array(' … ') + self::fillTypeParent($this->_object->company_id, 0, $this->_object->id)
			)
			->name('parent_id')
			->value($this->_object->parent_id)
			->caption(Core::_('Company_Location.parent_id'))
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-3'));

		$oMainRow3->add($oSelect_Types);

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @return self
	 * @hostcms-event Tag_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		if (!isset($this->_formValues['responsible_user_id']))
		{
			$this->_formValues['responsible_user_id'] = 0;
		}

		parent::_applyObjectProperty();

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));

		return $this;
	}

	/**
	 * Create visual tree of the types
	 * @param int $iParentId parent cell ID
	 * @param boolean $bExclude exclude cell ID
	 * @param int $iLevel current nesting level
	 * @return array
	 */
	static public function fillTypeParent($iCompanyId = 0, $iParentId = 0, $bExclude = FALSE, $iLevel = 0)
	{
		$iParentId = intval($iParentId);
		$iLevel = intval($iLevel);
		$iCompanyId = intval($iCompanyId);

		$oCompany_Location_Parent = Core_Entity::factory('Company_Location', $iParentId);

		$aReturn = array();

		// Дочерние элементы
		$childrenTypes = $oCompany_Location_Parent->Company_Locations->getAllByCompany_id($iCompanyId);
		// $childrenTypes = $childrenTypes->findAll();

		if (count($childrenTypes))
		{
			foreach ($childrenTypes as $childrenType)
			{
				if ($bExclude != $childrenType->id)
				{
					$aReturn[$childrenType->id] = str_repeat('  ', $iLevel) . $childrenType->name;
					$aReturn += self::fillTypeParent($iCompanyId, $childrenType->id, $bExclude, $iLevel + 1);
				}
			}
		}

		return $aReturn;
	}
}