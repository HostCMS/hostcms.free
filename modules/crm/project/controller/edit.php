<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Crm Project Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Crm
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Crm_Project_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		parent::setObject($object);

		$title = $this->_object->id
			? Core::_('Crm_Project.edit_title', $this->_object->name, FALSE)
			: Core::_('Crm_Project.add_title');

		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
			;

		$sColorValue = ($this->_object->id && $this->getField('color')->value)
			? $this->getField('color')->value
			: '#aebec4';

		$this->getField('color')
			->colorpicker(TRUE)
			->value($sColorValue);

		$oMainTab
			->move($this->getField('name')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow1)
			->move($this->getField('description')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow2)
			->move($this->getField('color')->set('data-control', 'hue')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-3')), $oMainRow3)
			->move($this->getField('datetime')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-4 col-lg-3')), $oMainRow3)
			->move($this->getField('deadline')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-4 col-lg-3')), $oMainRow3)
			->move($this->getField('completed')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-4 col-lg-3 margin-top-21')), $oMainRow3);

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		$countNotes = $this->_object->Crm_Project_Notes->getCount()
			? '<span class="badge badge-palegreen">' . $this->_object->Crm_Project_Notes->getCount() . '</span>'
			: '';

		ob_start();
		?>
		<div class="tabbable">
			<ul class="nav nav-tabs tabs-flat" id="crmProjectTabs">
				<li class="active">
					<a data-toggle="tab" href="#<?php echo $windowId?>_notes">
						<?php echo Core::_("Crm_Project.tabNotes")?> <?php echo $countNotes?>
					</a>
				</li>
			</ul>
			<div class="tab-content tabs-flat">
				<div id="<?php echo $windowId?>_notes" class="tab-pane in active">
					<?php
					Admin_Form_Entity::factory('Div')
						->controller($this->_Admin_Form_Controller)
						->id("crm-project-notes")
						->add(
							$this->_object->id
								? $this->_addNotes()
								: Admin_Form_Entity::factory('Code')->html(
									Core_Message::get(Core::_('Crm_Project.enable_after_save'), 'warning')
								)
						)
						->execute();
					?>
				</div>
			</div>
		</div>
		<?php
		$oMainRow4->add(Admin_Form_Entity::factory('Div')
			->class('form-group col-xs-12 margin-top-20')
			->add(
				Admin_Form_Entity::factory('Code')
					->html(ob_get_clean())
			)
		);

		$this->title($title);

		return $this;
	}

	/*
	 * Add event notes
	 * @return Admin_Form_Entity
	 */
	protected function _addNotes()
	{
		return Admin_Form_Entity::factory('Script')
			->value("$(function (){
				$.adminLoad({ path: '/admin/crm/project/note/index.php', additionalParams: 'crm_project_id=" . $this->_object->id . "', windowId: 'crm-project-notes' });
			});");
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Crm_Project_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		$bAddCrmProject = is_null($this->_object->id);

		parent::_applyObjectProperty();

		if ($bAddCrmProject)
		{
			ob_start();
			$this->_addNotes()->execute();
			$this->_Admin_Form_Controller->addMessage(ob_get_clean());
		}

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
	}
}