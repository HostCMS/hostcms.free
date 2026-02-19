<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Crm_Project_Note_Controller_Edit
 *
 * @package HostCMS
 * @subpackage Crm_Project
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Crm_Project_Note_Controller_Edit extends Crm_Note_Controller_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return Crm_Note_Controller_Edit
     */
	public function setObject($object)
	{
		$crm_project_id = Core_Array::getGet('crm_project_id', 0, 'int');

		$this->_relatedObject = Core_Entity::factory('Crm_Project')->getById($crm_project_id);

		if (is_null($this->_relatedObject))
		{
			throw new Core_Exception('_relatedObject is NULL.');
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

		$this->title($this->_object->id
			? Core::_('Crm_Project_Note.edit_title')
			: Core::_('Crm_Project_Note.add_title')
		);

		$oMainTab = $this->getTab('main');
		$oMainTab
			->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'));

		$oMainRow4->add(
			Admin_Form_Entity::factory('Code')
				->html('<input type="hidden" name="crm_project_id" value="' . $this->_relatedObject->id .'" />')
		);

		return $this;
	}

	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return bool
     */
	public function execute($operation = NULL)
	{
		$crm_project_id = Core_Array::getGet('crm_project_id', 0, 'int');

		$sJsRefresh = '<script>
			if ($("#id_content .timeline-crm").length && typeof _windowSettings != \'undefined\') {
				$.adminLoad({ path: hostcmsBackend + \'/crm/project/entity/index.php\', additionalParams: \'crm_project_id=' . $crm_project_id . '\', windowId: \'id_content\' });
			}
			if ($("#id_content #crm-project-notes").length) {
				$.adminLoad({ path: hostcmsBackend + \'/crm/project/note/index.php\', additionalParams: \'crm_project_id=' . $crm_project_id . '\', windowId: \'crm-project-notes\' });
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