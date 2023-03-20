<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Crm Project Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Crm
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Crm_Project_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
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
			->add(Admin_Form_Entity::factory('Div')->class('row')
				->add($oDivLeft = Admin_Form_Entity::factory('Div')->class('col-xs-12 col-md-6 col-lg-7 left-block'))
				->add($oDivRight = Admin_Form_Entity::factory('Div')->class('col-xs-12 col-md-6 col-lg-5 right-block'))
			);

			$oMainTab
			->add(Admin_Form_Entity::factory('Script')
				->value('
					$(function(){
						var timer = setInterval(function(){
							if ($("#' . $windowId . ' .left-block").height())
							{
								clearInterval(timer);

								$("#' . $windowId . ' .right-block").find("#' . $windowId . '_notes").slimscroll({
									height: $("#' . $windowId . ' .left-block").height() - 75,
									color: "rgba(0, 0, 0, 0.3)",
									size: "5px"
								});
							}
						}, 500);
					});
				'));

		$oDivLeft
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
			;

		$oDivRight
			->add($oMainRowRight1 = Admin_Form_Entity::factory('Div')->class('row'));

		$sColorValue = ($this->_object->id && $this->getField('color')->value)
			? $this->getField('color')->value
			: '#aebec4';

		$this->getField('color')
			->colorpicker(TRUE)
			->value($sColorValue);

		$oMainTab
			->move($this->getField('name')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow1)
			->move($this->getField('description')->divAttr(array('class' => 'form-group col-xs-12'))->rows(10), $oMainRow2)
			->move($this->getField('datetime')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-4 col-lg-6')), $oMainRow3)
			->move($this->getField('deadline')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-4 col-lg-6')), $oMainRow3)
			->move($this->getField('color')->set('data-control', 'hue')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')), $oMainRow4)
			->move($this->getField('completed')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-4 col-lg-6 margin-top-21')), $oMainRow4);

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		$countNotes = $this->_object->Crm_Notes->getCount()
			? '<span class="badge badge-palegreen">' . $this->_object->Crm_Notes->getCount() . '</span>'
			: '';

		ob_start();
		?>
		<div class="tabbable">
			<ul class="nav nav-tabs tabs-flat" id="crmProjectTabs">
				<li class="active">
					<a data-toggle="tab" href="#<?php echo $windowId?>_notes" data-path="/admin/crm/project/note/index.php" data-window-id="<?php echo $windowId?>_notes" data-additional="crm_project_id=<?php echo $this->_object->id?>">
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
		$oMainRowRight1->add(Admin_Form_Entity::factory('Div')
			->class('form-group col-xs-12 margin-top-20')
			->add(
				Admin_Form_Entity::factory('Code')
					->html(ob_get_clean())
			)
		);

		$this->title($this->_object->id
			? Core::_('Crm_Project.edit_title', $this->_object->name, FALSE)
			: Core::_('Crm_Project.add_title')
		);

		return $this;
	}

	/*
	 * Add event notes
	 * @return Admin_Form_Entity
	 */
	protected function _addNotes()
	{
		$windowId = $this->_Admin_Form_Controller->getWindowId();

		return Admin_Form_Entity::factory('Script')
			->value("$(function (){
				$.adminLoad({ path: '/admin/crm/project/note/index.php', additionalParams: 'crm_project_id=" . $this->_object->id . "', windowId: '{$windowId}_notes' });
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

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		if ($bAddCrmProject)
		{
			ob_start();
			$this->_addNotes()->execute();
			?>
			<script>
				$(function(){
					$("#<?php echo $windowId?> a[data-additional='crm_project_id=']").data('additional', 'crm_project_id=<?php echo $this->_object->id?>');
				});
			</script>
			<?php
			$this->_Admin_Form_Controller->addMessage(ob_get_clean());
		}

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
	}
}