<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Tpl Module.
 *
 * @package HostCMS
 * @subpackage Tpl
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Tpl_Module extends Core_Module_Abstract
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
	protected $_moduleName = 'tpl';

	/**
	 * Get Module's Menu
	 * @return array
	 */
	public function getMenu()
	{
		$this->menu = array(
			array(
				'sorting' => 100,
				'block' => 0,
				'ico' => 'fa-solid fa-code',
				'name' => Core::_('Tpl.menu'),
				'href' => Admin_Form_Controller::correctBackendPath("/{admin}/tpl/index.php"),
				'onclick' => Admin_Form_Controller::correctBackendPath("$.adminLoad({path: '/{admin}/tpl/index.php'}); return false")
			)
		);

		return parent::getMenu();
	}

	/**
	 * Функция обратного вызова для поисковой индексации
	 *
	 * @param int $site_id
	 * @param int $offset
	 * @param int $limit
	 * @return array
	 * @hostcms-event Tpl_Module.indexing
	 */
	public function indexing($site_id, $offset, $limit)
	{
		$offset = intval($offset);
		$limit = intval($limit);

		Core_Log::instance()->clear()
			->notify(FALSE)
			->status(Core_Log::$MESSAGE)
			->write("tpl indexing({$offset}, {$limit})");

		$oTpls = Core_Entity::factory('Tpl');
		$oTpls
			->queryBuilder()
			->leftJoin('tpl_dirs', 'tpls.tpl_dir_id', '=', 'tpl_dirs.id')
			->open()
				->where('tpl_dirs.id', 'IS', NULL)
				->setOr()
				->where('tpl_dirs.deleted', '=', 0)
			->close()
			->clearOrderBy()
			->orderBy('tpls.id', 'ASC')
			->limit($offset, $limit);

		Core_Event::notify(get_class($this) . '.indexing', $this, array($oTpls));

		$aTpls = $oTpls->findAll(FALSE);

		$aPages = array();
		foreach ($aTpls as $oTpl)
		{
			$aPages[] = $oTpl->indexing();
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

		$iAdmin_Form_Id = 236;
		$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);
		$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form)->formSettings();

		$sPath = '/{admin}/tpl/index.php';

		if ($oSearch_Page->module_value_id)
		{
			$oTpl = Core_Entity::factory('Tpl')->find($oSearch_Page->module_value_id);

			if (!is_null($oTpl->id))
			{
				$additionalParams = "tpl_dir_id={$oTpl->tpl_dir_id}";

				$href = $oAdmin_Form_Controller->getAdminActionLoadHref($sPath, 'edit', NULL, 1, $oTpl->id, $additionalParams);
				$onclick = $oAdmin_Form_Controller->getAdminActionLoadAjax($sPath, 'edit', NULL, 1, $oTpl->id, $additionalParams);
			}
		}

		return array(
			'icon' => 'fa fa-lightbulb-o',
			'href' => $href,
			'onclick' => $onclick
		);
	}
}