<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Calendar CALDAV Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Calendar
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Calendar_Caldav_User_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		$this
			->addSkipColumn('data')
			->addSkipColumn('synchronized_datetime');

		if (Core_Array::getGet('additionalSettings') == 1)
		{
			parent::setObject($object);

			// вызвать контроллер
			if (!is_null($this->_object->caldav_server)
				&& !is_null($this->_object->username)
				&& !is_null($this->_object->password)
			)
			{
				$Calendar_Caldav_Controller = Calendar_Caldav_Controller::instance($this->_object->Calendar_Caldav->driver);
				$Calendar_Caldav_Controller->showSecondWindow($this);
			}
		}
		else
		{
			if (!$object->id)
			{
				$oUser = Core_Auth::getCurrentUser();

				$calendar_caldav_id = intval(Core_Array::getGet('calendar_caldav_id'));

				$oCalendar_Caldav = Core_Entity::factory('Calendar_Caldav', $calendar_caldav_id);

				$oCalendar_Caldav_User = $oCalendar_Caldav->Calendar_Caldav_Users->getByUser_id($oUser->id);

				if ($oCalendar_Caldav_User)
				{
					$object = $oCalendar_Caldav_User;
				}
				else
				{
					$object->user_id = $oUser->id;
					$object->calendar_caldav_id = $calendar_caldav_id;
				}
			}

			parent::setObject($object);

			$oMainTab = $this->getTab('main');

			$oMainTab
				->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
				->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'));

			$this->title(
				$this->_object->id
					? Core::_('Calendar_Caldav.edit_title')
					: Core::_('Calendar_Caldav.add_title')
			);
		}

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @return self
	 * @hostcms-event Calendar_Caldav_User_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		if (is_null(Core_Array::getGet('additionalSettings')))
		{
			parent::_applyObjectProperty();
		}

		// вызвать контроллер
		if (!is_null($this->_object->caldav_server)
			&& !is_null($this->_object->username)
			&& !is_null($this->_object->password)
		)
		{
			$Calendar_Caldav_Controller = Calendar_Caldav_Controller::instance($this->_object->Calendar_Caldav->driver);
			$Calendar_Caldav_Controller
				->setUsername($this->_object->username)
				->setPassword($this->_object->password)
				->applyObjectProperty($this);
		}

		/*ob_start();
		print_r($_GET);
		print_r($_POST);
		$this->addContent(ob_get_clean());*/

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));

		return $this;
	}

}