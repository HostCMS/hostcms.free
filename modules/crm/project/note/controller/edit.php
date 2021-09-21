<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Crm_Project_Note_Controller_Edit
 *
 * @package HostCMS
 * @subpackage Crm
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Crm_Project_Note_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		$this
			->addSkipColumn('datetime')
			->addSkipColumn('user_id')
			->addSkipColumn('crm_project_id');

		parent::setObject($object);

		$this->title($this->_object->id
			? Core::_('Crm_Project_Note.edit_title')
			: Core::_('Crm_Project_Note.add_title'));

		$oMainTab = $this->getTab('main');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'));

		// $iCrmProjectId = intval(Core_Array::getGet('crmProjectId', 0));

		$oMainTab->move($this->getField('text'), $oMainRow1);

		$oMainRow2->add(
			Admin_Form_Entity::factory('Code')
				->html('<input type="hidden" name="crm_project_id" value="' . intval(Core_Array::getGet('crm_project_id', 0)) .'" />')
			);

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Crm_Project_Note_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		$iCrmProjectId = intval(Core_Array::getPost('crm_project_id'));

		// При добавлении комментария передаем идентификатор автора
		if (is_null($this->_object->id))
		{
			$oCurrentUser = Core_Auth::getCurrentUser();

			$this->_object->user_id = $oCurrentUser->id;

			$this->_object->crm_project_id = $iCrmProjectId;
		}

		parent::_applyObjectProperty();
	}

	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return mixed
	 */
	public function execute($operation = NULL)
	{
		$iCrmProjectId = Core_Array::getPost('crm_project_id', 0, 'int');

		// Всегда id_content
		$sJsRefresh = '<script>
			if ($("#id_content .timeline-crm").length && typeof _windowSettings != \'undefined\') {
				$.adminLoad({ path: \'/admin/crm/project/entity/index.php\', additionalParams: \'crm_project_id=' . $iCrmProjectId . '\', windowId: \'id_content\' });
			}
			if ($("#id_content #crm-project-notes").length) {
				$.adminLoad({ path: \'/admin/crm/project/note/index.php\', additionalParams: \'crm_project_id=' . $iCrmProjectId . '\', windowId: \'crm-project-notes\' });
			}
		</script>';

		switch ($operation)
		{
			case 'saveModal':
				$this->addMessage($sJsRefresh);
			break;
			case 'applyModal':
				$this->addContent($sJsRefresh);
			break;
		}

		return parent::execute($operation);
	}
}