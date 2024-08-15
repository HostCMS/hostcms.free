<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Bot_Entity_Model
 *
 * @package HostCMS
 * @subpackage Bot
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Bot_Entity_Model extends Core_Entity
{
	/**
	 * Disable markDeleted()
	 * @var mixed
	 */
	protected $_marksDeleted = NULL;

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'bot_module' => array()
	);

	/**
	 * Execute business logic
	 */
	public function execute()
	{
		$oBot_Module = $this->Bot_Module;
		$oBot = $oBot_Module->Bot;

		$oObject = Core_Entity::factory($this->model, $this->object_id);

		// Создаем класс бота
		$oClass = new $oBot->class();

		if ($oClass->available())
		{
			$oClass
				->setObject($oObject)
				->setBotModule($oBot_Module)
				->execute();

			$this->executed = 1;
			$this->save();
		}
		else
		{
			Core_Log::instance()->clear()
				->status(Core_Log::$ERROR)
				->write("Bot {$oBot->name} unavailable.");
		}
	}
}