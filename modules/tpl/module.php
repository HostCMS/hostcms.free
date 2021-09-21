<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Tpl Module.
 *
 * @package HostCMS
 * @subpackage Tpl
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Tpl_Module extends Core_Module
{
	/**
	 * Module version
	 * @var string
	 */
	public $version = '6.9';

	/**
	 * Module date
	 * @var date
	 */
	public $date = '2021-08-23';

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
				'ico' => 'fa fa-lightbulb-o',
				'name' => Core::_('Tpl.menu'),
				'href' => "/admin/tpl/index.php",
				'onclick' => "$.adminLoad({path: '/admin/tpl/index.php'}); return false"
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
	 * @hostcms-event Tpl_Module.indexing
	 */
	public function indexing($offset, $limit)
	{
		$offset = intval($offset);
		$limit = intval($limit);

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

		$result = array();
		foreach ($aTpls as $oTpl)
		{
			$result[] = $oTpl->indexing();
		}

		return $result;
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

		$sPath = '/admin/tpl/index.php';

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