<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Update Dataset.
 *
 * @package HostCMS
 * @subpackage Update
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2016 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Update_Dataset extends Admin_Form_Dataset
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

		$aReturn = Update_Controller::instance()->parseUpdates();

		$error = $aReturn['error'];
		$expiration_of_support = $aReturn['expiration_of_support'];
		$this->_objects = $aReturn['entities'];

		if ($error > 0 && $error != 5)
		{
			$this->_Admin_Form_Controller->addMessage(
				Core_Message::show(Core::_('Update.server_error_respond_' . $error), 'error')
			);
			//throw new Core_Exception(Core::_('Update.server_error_respond_' . $error), array(), 0, FALSE);
		}
		// Ошибок нет и количество обновления тоже 0
		elseif (count($this->_objects) == 0)
		{
			$this->_Admin_Form_Controller->addMessage(
				Core_Message::show(Core::_('Update.isLastUpdate'), 'message')
			);
		}

		if ($expiration_of_support && !defined('IS_HOSTCMS_FREE'))
		{
			$f_expiration_of_support = Core_Date::sql2date($expiration_of_support);

			$this->_Admin_Form_Controller->addMessage(
				Core_Date::sql2timestamp($expiration_of_support) > time()
					? Core_Message::get(Core::_('Update.support_available', $f_expiration_of_support))
					: Core_Message::get(Core::_('Update.support_has_expired', $f_expiration_of_support, 'www.hostcms.ru'), 'error')
			);
		}

		$this->_count = count($this->_objects);

		return $this;
	}

	/**
	 * Get new object
	 * @return object
	 */
	protected function _newObject()
	{
		return new Update_Entity();
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
			unset($this->_objects[$primaryKey]);
		}

		return $return;
	}
}