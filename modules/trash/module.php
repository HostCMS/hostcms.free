<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Trash Module.
 *
 * @package HostCMS
 * @subpackage Trash
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Trash_Module extends Core_Module_Abstract
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
	public $date = '2026-02-10';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'trash';

	/**
	 * Get List of Schedule Actions
	 * @return array
	 */
	public function getScheduleActions()
	{
		return array(
			0 => array(
				'name' => 'deleteAll'
			)
		);
	}

	/**
	 * Options
	 * @var array
	 */
	protected $_options = array(
		'maxExactCount' => array(
			'type' => 'int',
			'default' => 100000
		)
	);

	/**
	 * Get Module's Menu
	 * @return array
	 */
	public function getMenu()
	{
		$this->menu = array(
			array(
				'sorting' => 260,
				'block' => 3,
				'ico' => 'fa fa-trash-o',
				'name' => Core::_('trash.menu'),
				'href' => Admin_Form_Controller::correctBackendPath("/{admin}/trash/index.php"),
				'onclick' => Admin_Form_Controller::correctBackendPath("$.adminLoad({path: '/{admin}/trash/index.php'}); return false")
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
				$oTrash_Dataset = new Trash_Dataset();

				$oTrash_Dataset
					->offset(0)
					->limit(9999);

				$aTables = $oTrash_Dataset
					->fillTables()
					->getObjects();

				$offset = 0;
				$limit = 100;

				foreach ($aTables as $oTrash_Entity)
				{
					do {
						$iDeleted = $oTrash_Entity->chunkDelete($offset, $limit);

						$offset += ($limit - $iDeleted);
					} while ($iDeleted);

					$offset = 0;
				}
			break;
		}
	}
}