<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Bot_Controller.
 *
 * @package HostCMS
 * @subpackage Bot
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
abstract class Bot_Controller
{
	/**
	 * Bot module fields
	 * @var array
	 */
	protected $_fields = array();

	/**
	 * Get bot module fields
	 * @return array
	 */
	public function getFields()
	{
		return $this->_fields;
	}

	/**
	 * Bot module color
	 * @var string
	 */
	protected $_color = '#a0d468';

	/**
	 * Get bot module color
	 * @return string
	 */
	public function getColor()
	{
		return $this->_color;
	}

	/**
	 * Bot module object
	 * @var Bot_Module_Model
	 */
	protected $_oBot_Module = NULL;

	/**
	 * Set bot module
	 * @param string $oBot_Module Bot_Module_Model
	 * @return self
	 */
	public function setBotModule(Bot_Module_Model $oBot_Module)
	{
		$this->_oBot_Module = $oBot_Module;
		return $this;
	}

	/**
	 * Entity object
	 * @var _Model
	 */
	protected $_oObject = NULL;

	/**
	 * Set object
	 * @param string $oObject _Model
	 * @return self
	 */
	public function setObject($oObject)
	{
		$this->_oObject = $oObject;
		return $this;
	}

	/**
	 * Execute business logic
	 */
	abstract public function execute();

	/**
	 * Check available
	 */
	abstract public function available();

	/**
	 * Get bot modules
	 * @param int $module_id module id
	 * @param int $type entity type
	 * @param int $entity_id entity id
	 * @return array
	 */
	static public function getBotModules($module_id, $type, $entity_id)
	{
		$oBot_Modules = Core_Entity::factory('Bot_Module');
		$oBot_Modules->queryBuilder()
			->where('bot_modules.module_id', '=', $module_id)
			->where('bot_modules.type', '=', $type)
			->where('bot_modules.entity_id', '=', $entity_id);

		return $oBot_Modules->findAll(FALSE);
	}

	/**
	 * Get Site
	 * @param object $oObject
	 * @return Site_Model|NULL
	 */
	static public function getSite($oObject)
	{
		$oSite = NULL;

		if (isset($oObject->site_id))
		{
			$oSite = $oObject->Site;
		}
		elseif (isset($oObject->shop_id))
		{
			$oSite = $oObject->Shop->Site;
		}
		elseif (isset($oObject->informationsystem_id))
		{
			$oSite = $oObject->Informationsystem->Site;
		}

		return $oSite;
	}

	/**
	 * Notify bot
	 * @param int $module_id module id
	 * @param int $type entity type
	 * @param int $entity_id entity id
	 * @param object $object object
	 * @return self
	 */
	static public function notify($module_id, $type, $entity_id, $object)
	{
		$aBot_Modules = self::getBotModules($module_id, $type, $entity_id);

		foreach ($aBot_Modules as $oBot_Module)
		{
			if ($oBot_Module->delay_type == 0)
			{
				$timestamp = $oBot_Module->minutes
					? strtotime('+' . $oBot_Module->minutes . ' minutes')
					: time();
			}
			elseif ($oBot_Module->delay_type == 1)
			{
				if (method_exists($object, 'getStartDatetime'))
				{
					$tmpDatetime = Core_Date::sql2timestamp($object->getStartDatetime());
					$timestamp = strtotime('-' . $oBot_Module->minutes . ' minutes', $tmpDatetime);
				}
				else
				{
					Core_Log::instance()->clear()
						->status(Core_Log::$ERROR)
						->write('Unacceptable bot. Method getStartDatetime() is absent.');
					break;
				}
			}
			else
			{
				if (method_exists($object, 'getEndDatetime'))
				{
					$tmpDatetime = Core_Date::sql2timestamp($object->getEndDatetime());
					$timestamp = strtotime('-' . $oBot_Module->minutes . ' minutes', $tmpDatetime);
				}
				else
				{
					Core_Log::instance()->clear()
						->status(Core_Log::$ERROR)
						->write('Unacceptable bot. Method getEndDatetime() is absent.');
					break;
				}
			}

			$oBot_Entity = Core_Entity::factory('Bot_Entity');
			$oBot_Entity->bot_module_id = $oBot_Module->id;
			$oBot_Entity->object_id = $object->getPrimaryKey();
			$oBot_Entity->model = $object->getModelName();
			$oBot_Entity->datetime = Core_Date::timestamp2sql($timestamp);
			$oBot_Entity->save();

			if ($timestamp <= time())
			{
				$oBot_Entity->execute();
			}
		}
	}

	/**
	 * Get bot tab
	 * @param int $module_id module id
	 * @param int $entity_id entity id
	 * @param int $type entity type
	 * @return object
	 */
	static public function getBotTab($oModule, $entity_id, $type)
	{
		$oBotsTab = Admin_Form_Entity::factory('Tab')
			->caption(Core::_('Bot.tab_bots'))
			->name('Bots')
			// ->class($this->tabClass)
		;

		$oBotsTab->add($oBotsTabRow1 = Admin_Form_Entity::factory('Div')->class('row'));

		$sBotsContainerId = 'bots-container';

		$oDivBots = Admin_Form_Entity::factory('Div')
			->id($sBotsContainerId)
			->class('col-xs-12')
			->add(
				Admin_Form_Entity::factory('Script')
					->value("$(function (){
						$.adminLoad({ path: '" . Admin_Form_Controller::correctBackendPath('/{admin}/bot/module/index.php') ."', additionalParams: 'entity_id=" . $entity_id . "&module_id=" . $oModule->id . "&type=" . $type . "&hideMenu=1&_module=0', windowId: '{$sBotsContainerId}', loadingScreen: false });
					});")
			);

		$oBotsTabRow1->add($oDivBots);

		$oBot_Modules = Core_Entity::factory('Bot_Module');
		$oBot_Modules->queryBuilder()
			->where('bot_modules.module_id', '=', $oModule->id)
			->where('bot_modules.entity_id', '=', $entity_id)
			->where('bot_modules.type', '=', $type);

		$countBotModules = $oBot_Modules->getCount(FALSE);

		$countBotModules && $oBotsTab
			->badge($countBotModules)
			->badgeColor('azure');

		return $oBotsTab;
	}
}