<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Event_Note_Controller_Edit
 *
 * @package HostCMS
 * @subpackage Event
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Event_Note_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
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
			->addSkipColumn('event_id');

		parent::setObject($object);

		$this->title($this->_object->id
			? Core::_('Event_Note.edit_title')
			: Core::_('Event_Note.add_title'));

		$oMainTab = $this->getTab('main');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'));

		$iEventId = intval(Core_Array::getGet('eventId', 0));

		$oMainTab->move($this->getField('text'), $oMainRow1);

		$oMainRow2->add(
			Admin_Form_Entity::factory('Code')
				->html('<input type="hidden" name="event_id" value="' . $iEventId .'" />')
			);

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Event_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		$this->_object->datetime = Core_Date::timestamp2sql(time());

		$iEventId = intval(Core_Array::getPost('event_id'));

		// При добавлении комментария передаем идентификатор автора
		if (is_null($this->_object->id))
		{
			$oCurrentUser = Core_Auth::getCurrentUser();

			$this->_object->user_id = $oCurrentUser->id;

			$this->_object->event_id = $iEventId;
		}

		parent::_applyObjectProperty();

		// Связывание дела со сделкой
		if ($iEventId)
		{
			$oEvent = Core_Entity::factory('Event', $iEventId);

			$this
				->clearContent()
				->addContent('<script>$("#event-notes").replaceWith(\''. Core_Str::escapeJavascriptVariable($oEvent->showEventNotes()) . '\')</script>');
		}
	}

	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return mixed
	 */
	public function execute($operation = NULL)
	{
		$iEventId = intval(Core_Array::getGet('event_id'));

		$sJsRefresh = '<script>
			// Refresh event notes list
			if ($("#event-notes").length)
			{
				$.adminLoad({ path: \'/admin/event/note/index.php\', additionalParams: \'event_id=' . $iEventId . '\', windowId: \'event-notes\' });
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