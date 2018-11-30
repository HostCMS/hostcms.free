<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Template_Section_Lib_Controller_Edit
 *
 * @package HostCMS
 * @subpackage Template
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Template_Section_Lib_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Constructor.
	 * @param Admin_Form_Action_Model $oAdminFormAction action
	 */
	public function __construct(Admin_Form_Action_Model $oAdminFormAction)
	{
		parent::__construct($oAdminFormAction);
	}

	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		// При добавлении объекта
		if (!$object->id)
		{
			$object->template_section_id = Core_Array::getGet('template_section_id');
		}

		parent::setObject($object);

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		$this->title($this->_object->id
			? Core::_('Template_Section_Lib.widget_form_title_edit', $this->_object->widget())
			: Core::_('Template_Section_Lib.widget_form_title_add'));

		// Получаем основную вкладку
		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow5 = Admin_Form_Entity::factory('Div')->id('lib_properties'))			
			;

		$oAdditionalTab
			->add($oAdditionalRow1 = Admin_Form_Entity::factory('Div')->class('row'));

		$oAdditionalTab->delete($this->getField('lib_id'));
		$oMainTab->delete($this->getField('options'));

		$oMainTab
			->move($this->getField('class')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')), $oMainRow1)
			->move($this->getField('style')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')), $oMainRow1);

		$Lib_Controller_Edit = new Lib_Controller_Edit($this->_Admin_Form_Action);

		$oLib = Core_Entity::factory('Lib', $this->_object->lib_id);

		$Select_LibDir = Admin_Form_Entity::factory('Select')
			->name('lib_dir_id')
			->caption(Core::_('Template_Section_Lib.lib_dir_id'))
			->divAttr(array('id' => 'lib_dir', 'class' => 'form-group col-xs-12 col-lg-6'))
			->options(
				array(' … ') + $Lib_Controller_Edit->fillLibDir(0)
			)
			->value($oLib->lib_dir_id) //
			->onchange("$.ajaxRequest({path: '/admin/structure/index.php',context: 'lib_id', callBack: $.loadSelectOptionsCallback, action: 'loadLibList',additionalParams: 'lib_dir_id=' + this.value,windowId: '{$windowId}'}); return false");

		$aLibForDir = array(' … ');
		$aLibs = Core_Entity::factory('Lib_Dir', intval($oLib->lib_dir_id)) // Может быть NULL
			->Libs->findAll();

		foreach ($aLibs as $oTmpLib)
		{
			$aLibForDir[$oTmpLib->id] = '[' . $oTmpLib->id . '] ' . $oTmpLib->name;
		}
		$objectId = intval($this->_object->id);
		$Select_Lib = Admin_Form_Entity::factory('Select')
			->name('lib_id')
			->id('lib_id')
			->caption(Core::_('Template_Section_Lib.lib_id'))
			->divAttr(array('id' => 'lib', 'class' => 'form-group col-xs-12 col-lg-6'))
			->options($aLibForDir)
			->value($this->_object->lib_id)
			->onchange("$.ajaxRequest({path: '/admin/template/section/lib/index.php',context: 'lib_properties', callBack: $.loadDivContentAjaxCallback, objectId: {$objectId}, action: 'loadLibProperties',additionalParams: 'lib_id=' + this.value,windowId: '{$windowId}'}); return false")
			;

		$Select_Lib
			->add(
				Admin_Form_Entity::factory('A')
					->target('_blank')
					->href(
						$this->_Admin_Form_Controller->getAdminActionLoadHref('/admin/lib/index.php', 'edit', NULL, 1, $this->_object->lib_id, 'lib_dir_id=' . intval($oLib->lib_dir_id))
					)
					->class('input-group-addon bg-blue bordered-blue')
					->value('<i class="fa fa-pencil"></i>')
			);

		$Div_Lib_Properies = Admin_Form_Entity::factory('Code');

		ob_start();
		// DIV для св-в типовой дин. страницы
		// Для выбранного стандартно
		$Core_Html_Entity_Div = Core::factory('Core_Html_Entity_Script')
			->value("$('#{$windowId} #lib_id').change();")
			->execute();

		$Div_Lib_Properies
			->html(ob_get_clean());
			
		$oMainTab->move($this->getField('description')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow2);			

		$oMainRow3->add($Select_LibDir);		
		$oMainRow4->add($Select_Lib);
		$oMainRow5->add($Div_Lib_Properies);

		$oMainTab
			->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-3')), $oMainRow3)
			->move($this->getField('active')->divAttr(array('class' => 'margin-top-21 form-group col-xs-12 col-sm-6 col-md-3')), $oMainRow3);

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Template_Section_Lib_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		parent::_applyObjectProperty();

		// Lib properies
		if ($this->_object->lib_id)
		{
			$oLib = $this->_object->Lib;

			$JSON = Structure_Controller_Libproperties::getJson($oLib);

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
