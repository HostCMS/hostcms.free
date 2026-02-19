<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Wysiwyg_Handler
 *
 * @package HostCMS
 * @subpackage Wysiwyg
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
abstract class Wysiwyg_Handler
{
	/**
	 * The singleton instances.
	 * @var mixed
	 */
	static public $instance = array();

	/**
	 * Get full driver name
	 * @param string $driver driver name
	 * @return string
	 */
	static protected function _getDriverName($driver)
	{
		return 'Wysiwyg_Driver_' . ucfirst($driver) . '_Handler';
	}

	/**
	 * Create and return an object of cashregister
	 * @param string $driveName
	 * @param mixed $primaryKey Primary key
	 */
	static public function factory($driverName/*, $primaryKey = NULL*/)
	{
		$driver = self::_getDriverName($driverName);
		return new $driver();
	}

	/**
	 * Register an existing instance as a singleton.
	 * @param Wysiwyg_Model $oWysiwyg
	 * @return object
	 */
	static public function instance(Wysiwyg_Model $oWysiwyg)
	{
		if (!is_object($oWysiwyg))
		{
			throw new Core_Exception('Wrong argument type (expected Wysiwyg_Model)');
		}

		if (!isset(self::$instance[$oWysiwyg->id]))
		{
			$name = $oWysiwyg->driver;

			self::$instance[$oWysiwyg->id] = self::factory($name);
		}

		return self::$instance[$oWysiwyg->id];
	}

	/**
	 * Fill structure list
	 * @param int $iSiteId site ID
	 * @param int $iParentId parent node ID
	 * @param int $iLevel current nesting level
	 * @return array
	 */
	protected function _fillStructureList($iSiteId, $iParentId = 0, $iLevel = 0)
	{
		$iSiteId = intval($iSiteId);
		$iParentId = intval($iParentId);
		$iLevel = intval($iLevel);

		$aReturn = array();

		$oSite = Core_Entity::factory('Site', $iSiteId);

		$oStructures = $oSite->Structures;
		$oStructures->queryBuilder()
			->clearOrderBy()
			->orderBy('structures.sorting', 'ASC');

		$aChildren = $oStructures->getAllByparent_id($iParentId);
		foreach ($aChildren as $oStructure)
		{
			$oStructure->dataTitle = str_repeat('  ', $iLevel) . $oStructure->name;
			$aReturn[$oStructure->id] = $oStructure;
			$aReturn += $this->_fillStructureList($iSiteId, $oStructure->id, $iLevel + 1);
		}

		return $aReturn;
	}

	/**
	 * Init
	 * @param Admin_Form_Entity $oAdmin_Form_Entity_Textarea
	 */
	abstract public function init($oAdmin_Form_Entity_Textarea);

	/**
	 * Get driver js list
	 * @return array
	 */
	abstract public function getJsList();

	/**
	 * Get driver css list
	 * @return array
	 */
	abstract public function getCssList();

	/**
	 * Get driver raw js
	 * @return array
	 */
	abstract public function getJs();

	/**
	 * Get driver wysiwyg options config
	 * @return array|NULL
	 */
	abstract public function getConfig();

	/**
	 * Get exclude driver wysiwyg options config
	 * @return array
	 */
	abstract public function getExcludeOptions();
}