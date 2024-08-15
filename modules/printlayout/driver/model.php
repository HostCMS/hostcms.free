<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Printlayout_Driver_Model
 *
 * @package HostCMS
 * @subpackage Printlayout
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Printlayout_Driver_Model extends Core_Entity
{
	/**
	 * Name of the model
	 * @var string
	 */
	protected $_modelName = 'printlayout_driver';

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'printlayout_drivers.sorting' => 'ASC',
		'printlayout_drivers.name' => 'ASC',
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'user' => array()
	);

	/**
	 * Constructor.
	 * @param int $id entity ID
	 */
	public function __construct($id = NULL)
	{
		parent::__construct($id);

		if (is_null($id) && !$this->loaded())
		{
			$oUser = Core_Auth::getCurrentUser();
			$this->_preloadValues['user_id'] = is_null($oUser) ? 0 : $oUser->id;
		}
	}

	/**
	 * Change driver status
	 * @return self
	 * @hostcms-event printlayout_driver.onBeforeChangeActive
	 * @hostcms-event printlayout_driver.onAfterChangeActive
	 */
	public function changeActive()
	{
		Core_Event::notify($this->_modelName . '.onBeforeChangeActive', $this);

		$this->active = 1 - $this->active;
		$this->save();

		Core_Event::notify($this->_modelName . '.onAfterChangeActive', $this);

		return $this;
	}
}