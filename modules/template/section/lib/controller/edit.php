<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Template_Section_Lib_Controller_Edit
 *
 * @package HostCMS
 * @subpackage Template
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Template_Section_Lib_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		$this
			->addSkipColumn('options')
			->addSkipColumn('field_styles')
			->addSkipColumn('field_classes');

		// При добавлении объекта
		if (!$object->id)
		{
			$object->template_section_id = Core_Array::getGet('template_section_id');
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

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		$this->title($this->_object->id
			? Core::_('Template_Section_Lib.widget_form_title_edit', $this->_object->widget(), FALSE)
			: Core::_('Template_Section_Lib.widget_form_title_add')
		);

		// Получаем основную вкладку
		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow5 = Admin_Form_Entity::factory('Div')->id('lib_properties'));

		$oAdditionalTab
			->add($oAdditionalRow1 = Admin_Form_Entity::factory('Div')->class('row'));

		$oAdditionalTab->delete($this->getField('lib_id'));

		$oMainTab
			->move($this->getField('class')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow1)
			->move($this->getField('style')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow1);

		$Lib_Controller_Edit = new Lib_Controller_Edit($this->_Admin_Form_Action);

		$oLib = Core_Entity::factory('Lib', $this->_object->lib_id);

		$Select_LibDir = Admin_Form_Entity::factory('Select')
			->name('lib_dir_id')
			->caption(Core::_('Template_Section_Lib.lib_dir_id'))
			->divAttr(array('id' => 'lib_dir', 'class' => 'form-group col-xs-12 col-sm-4 col-md-6'))
			->options(
				array(' … ') + $Lib_Controller_Edit->fillLibDir(0)
			)
			->value($oLib->lib_dir_id)
			->onchange("$.ajaxRequest({path: hostcmsBackend + '/structure/index.php',context: 'lib_id', callBack: $.loadSelectOptionsCallback, action: 'loadLibList',additionalParams: 'lib_dir_id=' + this.value,windowId: '{$windowId}'}); return false");

		$aLibForDir = array(' … ');
		 // lib_dir_id может быть NULL
		$aLibs = Core_Entity::factory('Lib_Dir', intval($oLib->lib_dir_id))->Libs->findAll(FALSE);

		foreach ($aLibs as $oTmpLib)
		{
			$aLibForDir[$oTmpLib->id] = '[' . $oTmpLib->id . '] ' . $oTmpLib->name;
		}

		$objectId = intval($this->_object->id);
		$Select_Lib = Admin_Form_Entity::factory('Select')
			->name('lib_id')
			->id('lib_id')
			->caption(Core::_('Template_Section_Lib.lib_id'))
			->divAttr(array('id' => 'lib', 'class' => 'form-group col-xs-12 col-sm-8 col-md-6'))
			->options($aLibForDir)
			->value($this->_object->lib_id)
			->onchange("$.ajaxRequest({path: hostcmsBackend + '/template/section/lib/index.php', context: '{$this->_formId}', callBack: $.loadDivContentAjaxCallback, objectId: {$objectId}, action: 'loadLibProperties',additionalParams: 'lib_id=' + this.value,windowId: '{$windowId}'}); return false");

		$Select_Lib
			->add(
				Admin_Form_Entity::factory('A')
					->target('_blank')
					->href(
						$this->_object->lib_id
							? $this->_object->lib_id . $this->_Admin_Form_Controller->getAdminActionLoadHref('/{admin}/lib/index.php', 'edit', NULL, 1, $this->_object->lib_id, 'lib_dir_id=' . intval($oLib->lib_dir_id))
							: ''
					)
					->class('lib-edit input-group-addon blue' . ($this->_object->lib_id ? '' : ' hidden'))
					->value('<i class="fa fa-pencil"></i>')
			);

		$Div_Lib_Properties = Admin_Form_Entity::factory('Code');

		ob_start();
		// DIV для св-в типовой дин. страницы
		// Для выбранного стандартно
		Core_Html_Entity::factory('Script')
			->value("$('#{$windowId} #lib_id').change();")
			->execute();

		$Div_Lib_Properties
			->html(ob_get_clean());

		$oMainTab->move($this->getField('description')->rows(2)->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow2);

		$oMainRow3->add($Select_LibDir);
		$oMainRow4->add($Select_Lib);
		$oMainRow5->add($Div_Lib_Properties);

		$oMainTab
			->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4 col-md-3')), $oMainRow3)
			->move($this->getField('active')->divAttr(array('class' => 'margin-top-21 form-group col-xs-12 col-sm-4 col-md-3')), $oMainRow3);

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Template_Section_Lib_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		// Backup revision
		if (Core::moduleIsActive('revision') && $this->_object->id)
		{
			$this->_object->backupRevision();
		}

		parent::_applyObjectProperty();

		// Lib properties
		if ($this->_object->lib_id)
		{
			$JSON = Structure_Controller_Libproperties::getJson($this->_object);

			// Сохраняем настройки
			$this->_object->options = $JSON;
			$this->_object->save();
		}

		// Frontend behaviour
		if (Core_Array::getRequest('hostcmsMode') == 'blank')
		{
			?><script>
			window.parent.hQuery.refreshSection(<?php echo $this->_object->template_section_id?>);
			$(window.frameElement).parentsUntil('.ui-dialog').parent().find(".ui-dialog-titlebar-close").trigger('click');
			</script><?php
		}

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
	}
}
