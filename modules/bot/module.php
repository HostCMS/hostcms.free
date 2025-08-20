<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Bot Module.
 *
 * @package HostCMS
 * @subpackage Bot
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Bot_Module extends Core_Module_Abstract
{
	/**
	 * Module version
	 * @var string
	 */
	public $version = '7.1';

	/**
	 * Module date
	 * @var date
	 */
	public $date = '2025-08-19';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'bot';

	/**
	 * Get List of Schedule Actions
	 * @return array
	 */
	public function getScheduleActions()
	{
		return array(
			0 => array(
				'name' => 'executeBotEntities',
				'entityCaption' => ''
			)
		);
	}

	/**
	 * Get Module's Menu
	 * @return array
	 */
	public function getMenu()
	{
		$this->menu = array(
			array(
				'sorting' => 100,
				'block' => 0,
				'ico' => 'fa fa-android',
				'name' => Core::_('Bot.menu'),
				'href' => Admin_Form_Controller::correctBackendPath("/{admin}/bot/index.php"),
				'onclick' => Admin_Form_Controller::correctBackendPath("$.adminLoad({path: '/{admin}/bot/index.php'}); return false")
			)
		);

		return parent::getMenu();
	}

	/**
	 * Notify module on the action on schedule
	 * @param Schedule_Model $oSchedule
	 */
	public function callSchedule($oSchedule)
	{
		$action = $oSchedule->action;

		switch ($action)
		{
			case 0:
				$oBot_Entities = Core_Entity::factory('Bot_Entity');
				$oBot_Entities->queryBuilder()
					->where('bot_entities.executed', '=', 0)
					->where('bot_entities.datetime', '<=', Core_Date::timestamp2sql(time()))
					->clearOrderBy()
					->orderBy('id', 'ASC');

				$oBot_Entities->chunk(100, function ($aBot_Entities, $step) {
					foreach ($aBot_Entities as $oBot_Entity)
					{
						$oBot_Entity->execute();
					}
				});
			break;
		}
	}
}