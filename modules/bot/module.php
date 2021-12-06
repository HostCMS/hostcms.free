<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Bot Module.
 *
 * @package HostCMS
 * @subpackage Bot
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Bot_Module extends Core_Module
{
	/**
	 * Module version
	 * @var string
	 */
	public $version = '7.0';

	/**
	 * Module date
	 * @var date
	 */
	public $date = '2021-12-03';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'bot';

	/**
	 * List of Schedule Actions
	 * @var array
	 */
	protected $_scheduleActions = array(
		0 => 'executeBotEntities'
	);

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
				'href' => "/admin/bot/index.php",
				'onclick' => "$.adminLoad({path: '/admin/bot/index.php'}); return false"
			)
		);

		return parent::getMenu();
	}

	/**
	 * Notify module on the action on schedule
	 * @param int $action action number
	 * @param int $entityId entity ID
	 * @return array
	 */
	public function callSchedule($action, $entityId)
	{
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