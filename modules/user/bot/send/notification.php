<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * User_Bot_Send_Notification
 *
 * @package HostCMS
 * @subpackage User
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class User_Bot_Send_Notification extends Bot_Controller
{
	/**
	 * Bot module color
	 * @var string
	 */
	protected $_color = '#bc5679';

	/**
	 * Get bot module fields
	 * @return array
	 */
	public function getFields()
	{
		$this->_fields = array(
			'responsible' => array(
				'caption' => Core::_('User.responsible'),
				'type' => 'checkbox',
				'value' => TRUE,
				'obligatory' => FALSE
			),
			'employees' => array(
				'caption' => Core::_('User.additional_receivers'),
				'type' => 'users',
				'obligatory' => FALSE
			),
			'title' => array(
				'caption' => Core::_('User.title'),
				'type' => 'input',
				'value' => FALSE,
				'obligatory' => TRUE
			),
			'description' => array(
				'caption' => Core::_('User.text'),
				'type' => 'textarea',
				'value' => FALSE,
				'obligatory' => TRUE
			)
		);

		return parent::getFields();
	}

	/**
	 * Check available
	 */
	public function available()
	{
		return TRUE;
	}

	/**
	 * Execute business logic
	 */
	public function execute()
	{
		$aSettings = json_decode($this->_oBot_Module->json, TRUE);

		$aResponsibleUsers = array();

		if (method_exists($this->_oObject, 'getResponsibleUsers'))
		{
			$aResponsibleUsers = isset($aSettings['responsible']) && $aSettings['responsible']
				? $this->_oObject->getResponsibleUsers()
				: array();
		}
		else
		{
			Core_Log::instance()->clear()
				->status(Core_Log::$ERROR)
				->write("User_Bot_Send_Notification: method getResponsibleUsers() doesn`t exist in model");
		}

		if (isset($aSettings['employees']))
		{
			foreach ($aSettings['employees'] as $user_id)
			{
				$oUser = Core_Entity::factory('User')->getById($user_id);

				if (!is_null($oUser) && !in_array($oUser, $aResponsibleUsers))
				{
					$aResponsibleUsers[] = $oUser;
				}
			}
		}

		foreach ($aResponsibleUsers as $oUser)
		{
			$oCore_Meta = new Core_Meta();
			$oCore_Meta
				->addObject('user', $oUser)
				->addObject('object', $this->_oObject)
				->addObject('settings', $aSettings);

			$sTitle = isset($aSettings['title']) && strlen(trim($aSettings['title']))
				? $oCore_Meta->apply($aSettings['title'])
				: Core::_('Admin_Form.non_subject');

			$sDescription = isset($aSettings['description'])
				? $oCore_Meta->apply($aSettings['description'])
				: NULL;

			if (!is_null($sDescription))
			{
				$oModule = Core::$modulesList['user'];

				$oNotification = Core_Entity::factory('Notification');
				$oNotification
					->title($sTitle)
					->description($sDescription)
					->datetime(Core_Date::timestamp2sql(time()))
					->module_id($oModule->id)
					->type(3) // 3 - уведомление от бота
					->entity_id($oUser->id)
					->save();

				// Связываем уведомление с сотрудником
				$oUser->add($oNotification);

				Core_Log::instance()->clear()
					->status(Core_Log::$SUCCESS)
					->write("User_Bot_Send_Notification: notification sent to user id: {$oUser->id}");
			}
		}
	}
}