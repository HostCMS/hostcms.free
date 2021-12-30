<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * XSL Module.
 *
 * @package HostCMS
 * @subpackage Xsl
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Xsl_Module extends Core_Module
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
	public $date = '2021-12-03';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'xsl';

	protected $_options = array(
		'formatOutput' => array(
			'type' => 'checkbox',
			'default' => TRUE
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
				'sorting' => 100,
				'block' => 0,
				'ico' => 'fa fa-code',
				'name' => Core::_('Xsl.menu'),
				'href' => "/admin/xsl/index.php",
				'onclick' => "$.adminLoad({path: '/admin/xsl/index.php'}); return false"
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
	 * @hostcms-event Xsl_Module.indexing
	 */
	public function indexing($offset, $limit)
	{
		$offset = intval($offset);
		$limit = intval($limit);

		$oXsls = Core_Entity::factory('Xsl');
		$oXsls
			->queryBuilder()
			->leftJoin('xsl_dirs', 'xsls.xsl_dir_id', '=', 'xsl_dirs.id')
			->open()
				->where('xsl_dirs.id', 'IS', NULL)
				->setOr()
				->where('xsl_dirs.deleted', '=', 0)
			->close()
			->clearOrderBy()
			->orderBy('xsls.id', 'ASC')
			->limit($offset, $limit);

		Core_Event::notify(get_class($this) . '.indexing', $this, array($oXsls));

		$aXsls = $oXsls->findAll(FALSE);

		$result = array();
		foreach ($aXsls as $oXsl)
		{
			$result[] = $oXsl->indexing();
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

		$iAdmin_Form_Id = 22;
		$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);
		$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form)->formSettings();

		$sPath = '/admin/xsl/index.php';

		if ($oSearch_Page->module_value_id)
		{
			$oXsl = Core_Entity::factory('Xsl')->find($oSearch_Page->module_value_id);

			if (!is_null($oXsl->id))
			{
				$additionalParams = "xsl_dir_id={$oXsl->xsl_dir_id}";

				$href = $oAdmin_Form_Controller->getAdminActionLoadHref($sPath, 'edit', NULL, 1, $oXsl->id, $additionalParams);
				$onclick = $oAdmin_Form_Controller->getAdminActionLoadAjax($sPath, 'edit', NULL, 1, $oXsl->id, $additionalParams);
			}
		}

		return array(
			'icon' => 'fa fa-code',
			'href' => $href,
			'onclick' => $onclick
		);
	}
}