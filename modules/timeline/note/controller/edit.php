<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Timeline_Note_Controller_Edit
 *
 * @package HostCMS
 * @subpackage Timeline
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Timeline_Note_Controller_Edit extends Crm_Note_Controller_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return Crm_Note_Controller_Edit
	 */
	public function setObject($object)
	{
		$this->_relatedObject = Core_Entity::factory('Timeline')->getByCrm_note_id($object->id);

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
			? Core::_('Timeline_Note.edit_title')
			: Core::_('Timeline_Note.add_title')
		);

		$oMainTab = $this->getTab('main');
		$oMainTab
			->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'));

		$oMainRow4->add(
			Admin_Form_Entity::factory('Code')
				->html('<input type="hidden" name="timeline_id" value="' . $this->_relatedObject->id .'" />')
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
		$sJsRefresh = '<script>
			if ($("#id_content .timeline-wrapper").length && typeof _windowSettings != \'undefined\') {
				$.adminLoad({ path: hostcmsBackend + \'/timeline/index.php\', additionalParams: \'\', windowId: \'id_content\' });
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