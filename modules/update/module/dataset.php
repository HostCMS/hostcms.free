<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Update Dataset.
 *
 * @package HostCMS
 * @subpackage Update
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Update_Module_Dataset extends Admin_Form_Dataset
{
	/**
	 * Items count
	 * @var int
	 */
	protected $_count = NULL;

	/**
	 * Get count of finded objects
	 * @return int
	 */
	public function getCount()
	{
		if (is_null($this->_count))
		{
			try
			{
				$this->_getUpdates();
			}
			catch (Exception $e)
			{
				Core_Message::show($e->getMessage(), 'error');
			}
		}

		return $this->_count;
	}

	/**
	 * Dataset objects list
	 * @var array
	 */
	protected $_objects = array();

	/**
	 * Load objects
	 * @return array
	 */
	public function load()
	{
		return array_slice($this->_objects, $this->_offset, $this->_limit);
	}

	/**
	 * Get updates
	 * @return self
	 */
	protected function _getUpdates()
	{
		$this->_objects = array();
		$this->_count = 0;

		$aReturn = Update_Controller::instance()->parseModules();

		$this->_objects = $aReturn;

		$this->_count = count($this->_objects);

		return $this;
	}

	/**
	 * Get new object
	 * @return object
	 */
	protected function _newObject()
	{
		return new Update_Module_Entity();
	}

	/**
	 * Get entity
	 * @return object
	 */
	public function getEntity()
	{
		return $this->_newObject();
	}

	/**
	 * Get object
	 * @param int $primaryKey ID
	 * @return object
	 */
	public function getObject($primaryKey)
	{
		$primaryKey != 0 && !$this->_count && $this->_getUpdates();

		$return = isset($this->_objects[$primaryKey])
			? $this->_objects[$primaryKey]
			: $this->_newObject();

		if (isset($this->_objects[$primaryKey]))
		{
			$oModule = Core_Entity::factory('Module')->getByPath($this->_objects[$primaryKey]->path);

			if ($oModule)
			{
				$oModule->Core_Module->version = $this->_objects[$primaryKey]->number;
			}

			//unset($this->_objects[$primaryKey]);
			$this->_objects = array();
			$this->_count = NULL;
		}

		return $return;
	}
}