<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Lib Module.
 *
 * @package HostCMS
 * @subpackage Lib
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Lib_Module extends Core_Module
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
	public $date = '2023-07-17';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'lib';
	
	/**
	 * Get Module's Menu
	 * @return array
	 */
	public function getMenu()
	{
		$this->menu = array(
			array(
				'sorting' => 90,
				'block' => 0,
				'ico' => 'fa fa-briefcase',
				'name' => Core::_('lib.menu'),
				'href' => "/admin/lib/index.php",
				'onclick' => "$.adminLoad({path: '/admin/lib/index.php'}); return false"
			)
		);

		return parent::getMenu();
	}
	
	/**
	 * Функция обратного вызова для поисковой индексации
	 *
	 * @param $offset
	 * @param $limit
	 * @return array
	 * @hostcms-event Lib_Module.indexing
	 */
	public function indexing($offset, $limit)
	{
		$offset = intval($offset);
		$limit = intval($limit);

		Core_Log::instance()->clear()
			->notify(FALSE)
			->status(Core_Log::$MESSAGE)
			->write("lib indexing({$offset}, {$limit})");

		$oLibs = Core_Entity::factory('Lib');
		$oLibs
			->queryBuilder()
			->leftJoin('lib_dirs', 'libs.lib_dir_id', '=', 'lib_dirs.id')
			->open()
				->where('lib_dirs.id', 'IS', NULL)
				->setOr()
				->where('lib_dirs.deleted', '=', 0)
			->close()
			->clearOrderBy()
			->orderBy('libs.id', 'ASC')
			->limit($offset, $limit);

		Core_Event::notify(get_class($this) . '.indexing', $this, array($oLibs));

		$aLibs = $oLibs->findAll(FALSE);

		$aPages = array();
		foreach ($aLibs as $oLib)
		{
			$aPages[] = $oLib->indexing();
		}

		return array('pages' => $aPages, 'indexed' => count($aPages), 'finished' => count($aPages) < $limit);
	}

	/**
	 * Backend search callback function
	 * @param Search_Page_Model $oSearch_Page
	 * @return array 'href' and 'onclick'
	 */
	public function backendSearchCallback($oSearch_Page)
	{
		$href = $onclick = NULL;

		$iAdmin_Form_Id = 32;
		$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);
		$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form)->formSettings();

		$sPath = '/admin/lib/index.php';

		if ($oSearch_Page->module_value_id)
		{
			$oLib = Core_Entity::factory('Lib')->find($oSearch_Page->module_value_id);

			if (!is_null($oLib->id))
			{
				$additionalParams = "lib_dir_id={$oLib->lib_dir_id}";

				$href = $oAdmin_Form_Controller->getAdminActionLoadHref($sPath, 'edit', NULL, 1, $oLib->id, $additionalParams);
				$onclick = $oAdmin_Form_Controller->getAdminActionLoadAjax($sPath, 'edit', NULL, 1, $oLib->id, $additionalParams);
			}
		}

		return array(
			'icon' => 'fa fa-file-code-o',
			'href' => $href,
			'onclick' => $onclick
		);
	}
}