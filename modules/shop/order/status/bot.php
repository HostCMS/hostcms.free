<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Order_Status_Bot
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Shop_Order_Status_Bot extends Bot_Controller
{
	/**
	 * Bot module color
	 * @var string
	 */
	protected $_color = '#2dc3e8';
	
	/**
	 * Get bot module fields
	 * @return array
	 */
	public function getFields()
	{
		$this->_fields = array(
			'working-hours' => array(
				'caption' => Core::_('Shop_Order_Status.working_hours'),
				'type' => 'checkbox',
				'value' => FALSE,
				'obligatory' => FALSE,
			),
			'employees' => array(
				'caption' => Core::_('Shop_Order_Status.employees'),
				'type' => 'users',
				'obligatory' => TRUE,
			),
			'on-duty' => array(
				'caption' => Core::_('Shop_Order_Status.on_duty'),
				'type' => 'users',
				'obligatory' => FALSE,
			)
		);

		return parent::getFields();
	}

	/**
	 * Check available
	 */
	public function available()
	{
		return Core::moduleIsActive('shop');
	}

	/**
	 * Execute business logic
	 */
	public function execute()
	{
		if (!is_null($this->_oObject) && get_class($this->_oObject) == 'Shop_Order_Model')
		{
			$aSettings = json_decode($this->_oBot_Module->json, TRUE);

			$bWorkingHours = isset($aSettings['working-hours']) && $aSettings['working-hours'];

			if (isset($aSettings['employees']))
			{
				shuffle($aSettings['employees']);

				foreach ($aSettings['employees'] as $user_id)
				{
					$oUser = Core_Entity::factory('User')->getById($user_id);

					// 2 - рабочий день начат, сотрудник работает
					// 5 - рабочий день окончен, но не завершен сотрудником
					if (!$bWorkingHours || $oUser->getStatusWorkday() == 2 || $oUser->getStatusWorkday() == 5)
					{
						$this->_oObject->user_id = $oUser->id;
						$this->_oObject->save();

						$this->_oObject->historyPushChangeUser();

						return TRUE;
					}
				}
			}

			if (isset($aSettings['on-duty']))
			{
				shuffle($aSettings['on-duty']);

				foreach ($aSettings['on-duty'] as $user_id)
				{
					$oUser = Core_Entity::factory('User')->getById($user_id);

					// 2 - рабочий день начат, сотрудник работает
					// 5 - рабочий день окончен, но не завершен сотрудником
					if (!$bWorkingHours || $oUser->getStatusWorkday() == 2 || $oUser->getStatusWorkday() == 5)
					{
						$this->_oObject->user_id = $oUser->id;
						$this->_oObject->save();

						$this->_oObject->historyPushChangeUser();

						return TRUE;
					}
				}
			}
		}
		else
		{
			Core_Log::instance()->clear()
				->status(Core_Log::$ERROR)
				->write('Shop_Order_Status_Bot: object is NULL or wrong type. Must be Shop_Order_Model');
		}
	}
}