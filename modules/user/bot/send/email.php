<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * User_Bot_Send_Email
 *
 * @package HostCMS
 * @subpackage User
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class User_Bot_Send_Email extends Bot_Controller
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
			'from' => array(
				'caption' => Core::_('User.from'),
				'type' => 'input',
				'value' => FALSE,
				'obligatory' => TRUE
			),
			'sender-name' => array(
				'caption' => Core::_('User.sender_name'),
				'type' => 'input',
				'value' => FALSE,
				'obligatory' => TRUE
			),
			'theme' => array(
				'caption' => Core::_('User.theme'),
				'type' => 'input',
				'value' => FALSE,
				'obligatory' => TRUE
			),
			'text' => array(
				'caption' => Core::_('User.text'),
				'type' => 'wysiwyg',
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
				->write("User_Bot_Send_Email: method getResponsibleUsers() doesn`t exist in model");
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

		$sFrom = isset($aSettings['from']) && strlen(trim($aSettings['from']))
			? $aSettings['from']
			: NULL;

		$sSenderName = isset($aSettings['sender-name']) && strlen(trim($aSettings['sender-name']))
			? $aSettings['sender-name']
			: NULL;

		foreach ($aResponsibleUsers as $oUser)
		{
			$oCore_Meta = new Core_Meta();
			$oCore_Meta
				->addObject('user', $oUser)
				->addObject('object', $this->_oObject)
				->addObject('settings', $aSettings);

			$sSubject = isset($aSettings['theme']) && strlen(trim($aSettings['theme']))
				? $oCore_Meta->apply($aSettings['theme'])
				: Core::_('Admin_Form.non_subject');

			$sMessage = isset($aSettings['text'])
				? $oCore_Meta->apply($aSettings['text'])
				: NULL;

			$aDirectory_Emails = $oUser->Directory_Emails->findAll(FALSE);
			$email = isset($aDirectory_Emails[0])
				? htmlspecialchars($aDirectory_Emails[0]->value)
				: NULL;

			if (!is_null($email))
			{
				Core_Mail::instance()
					->clear()
					->to($email)
					->from($sFrom)
					->senderName($sSenderName)
					->subject($sSubject)
					->message($sMessage)
					->contentType('text/html')
					->header('X-HostCMS-Reason', 'User Bot Send Email')
					->header('Precedence', 'bulk')
					->send();

				Core_Log::instance()->clear()
					->status(Core_Log::$SUCCESS)
					->write("User_Bot_Send_Email: mail sent to user id: {$oUser->id}, email: {$email}");
			}
		}
	}
}