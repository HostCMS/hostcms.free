<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Bot_Module_Model
 *
 * @package HostCMS
 * @subpackage Bot
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Bot_Module_Model extends Core_Entity
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
		'bot' => array(),
		'module' => array()
	);

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'bot_entity' => array()
	);

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'bot_modules.sorting' => 'ASC'
	);

	/**
	 * Get deadline
	 * @return string
	 */
	public function getDeadline()
	{
		$deadline = '';

		switch ($this->delay_type)
		{
			case 0:
				$deadline = Core::_('Bot_Module.delay_type0');
			break;
			case 1:
				$deadline = Core::_('Bot_Module.delay_type1');
			break;
			case 2:
				$deadline = Core::_('Bot_Module.delay_type2');
			break;
			case 3:
			default:
				$deadline = Core::_('Bot_Module.delay_type3');
			break;
		}

		if ($this->delay_type)
		{
			$deadline .= ' ' . Core_Date::time2string($this->minutes * 60);
		}

		return $deadline;
	}

	/**
	 * Get deadline class
	 * @return string
	 */
	public function getDeadlineClass()
	{
		$class = '';

		switch ($this->delay_type)
		{
			case 0:
				$class = 'success';
			break;
			case 1:
				$class = 'blue';
			break;
			case 2:
				$class = 'blueberry';
			break;
			case 3:
			default:
				$class = 'danger';
			break;
		}

		return $class;
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event bot_module.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->Bot_Entities->deleteAll(FALSE);

		return parent::delete($primaryKey);
	}
}