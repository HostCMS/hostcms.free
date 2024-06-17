<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Schedule
 * Типовой контроллер загрузки значений списка для select
 *
 * @package HostCMS
 * @subpackage Schedule
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Schedule_Load_Select_Options extends Admin_Form_Action_Controller
{
	/**
	 * Module id
	 * @var Core_Entity
	 */
	protected $_moduleId = NULL;

	/**
	 * Set model
	 * @param Core_Entity $model object
	 * @return self
	 */
	public function moduleId($moduleId)
	{
		$this->_moduleId = $moduleId;
		return $this;
	}

	/**
	 * Default value
	 * @var string
	 */
	protected $_defaultValue = NULL;

	/**
	 * Set default value
	 * @param string|int $defaultValue default value
	 * @return self
	 */
	public function defaultValue($defaultValue)
	{
		$this->_defaultValue = $defaultValue;
		return $this;
	}

	/**
	 * Array of values
	 * @var array
	 */
	protected $_values = array();

	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 */
	public function execute($operation = NULL)
	{
		!is_null($this->_defaultValue) && $this->_values[] = $this->_defaultValue;

		// Add objects
		$this->addValues();

		Core::showJson($this->_values);
	}

	/**
	 * Add value
	 * @return self
	 */
	public function addValues()
	{
		$oSchedule_Controller = new Schedule_Controller();

		$aActions = $oSchedule_Controller->getModuleActions($this->_moduleId);

		// count($aActions) && $this->_values = $aActions;

		if (count($aActions))
		{
			$this->_values = array();

			foreach ($aActions as $key => $aAction)
			{
				$aTmp = array(
					'value' => $key,
					'name' => $aAction['value']
				);

				isset($aAction['attr'])
					&& $aTmp['attr'] = $aAction['attr'];

				$this->_values[] = $aTmp;
			}
		}

		return $this;
	}
}