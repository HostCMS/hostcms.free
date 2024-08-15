<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Directory_Phone_Model
 *
 * @package HostCMS
 * @subpackage Directory
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Directory_Phone_Format_Model extends Core_Entity
{
	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'directory_phone_format';

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'user' => array()
	);

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'directory_phone_formats.id' => 'ASC'
	);

	/**
	 * Change item status
	 * @return self
	 * @hostcms-event directory_phone_format.onBeforeChangeActive
	 * @hostcms-event directory_phone_format.onAfterChangeActive
	 */
	public function changeActive()
	{
		Core_Event::notify($this->_modelName . '.onBeforeChangeActive', $this);

		$this->active = 1 - $this->active;
		$this->save();

		Core_Event::notify($this->_modelName . '.onAfterChangeActive', $this);

		return $this;
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function sampleBackend()
	{
		if ($this->sample !== '')
		{
			return htmlspecialchars($this->sample) . ' ⟶ ' . htmlspecialchars($this->applyPattern($this->sample));
		}
	}

	public function applyPattern($phone)
	{
		// Оставляем солько цифры и + до применения шаблона
		$result = preg_replace("/^{$this->from}$/", $this->to, preg_replace('/[^0-9+]/', '', $phone), -1, $count);

		return $count > 0 ? $result : $phone;
	}
}