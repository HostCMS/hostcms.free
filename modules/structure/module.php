<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Structure Module.
 *
 * @package HostCMS
 * @subpackage Structure
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Structure_Module extends Core_Module
{
	/**
	 * Module version
	 * @var string
	 */
	public $version = '6.7';

	/**
	 * Module date
	 * @var date
	 */
	public $date = '2018-03-02';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'structure';

	/**
	 * Get Module's Menu
	 * @return array
	 */
	public function getMenu()
	{
		$this->menu = array(
			array(
				'sorting' => 10,
				'block' => 0,
				'ico' => 'fa fa-sitemap',
				'name' => Core::_('Structure.menu'),
				'href' => "/admin/structure/index.php",
				'onclick' => "$.adminLoad({path: '/admin/structure/index.php'}); return false"
			)
		);

		return parent::getMenu();
	}

	/**
	 * Индексация структуры сайта
	 *
	 * @param $offset
	 * @param $limit
	 * @return array
	 * @hostcms-event Structure_Module.indexing
	 */
	public function indexing($offset, $limit)
	{
		$offset = intval($offset);
		$limit = intval($limit);

		$oStructure = Core_Entity::factory('Structure');

		$oStructure
			->queryBuilder()
			->join('sites', 'structures.site_id', '=', 'sites.id')
			->where('structures.active', '=', 1)
			->where('structures.indexing', '=', 1)
			->where('structures.path', '!=', '')
			->where('structures.url', '=', '')
			->where('sites.deleted', '=', 0)
			->where('sites.active', '=', 1)
			->orderBy('structures.id', 'DESC')
			->limit($offset, $limit);

		Core_Event::notify(get_class($this) . '.indexing', $this, array($oStructure));

		$aStructures = $oStructure->findAll(FALSE);

		$result = array();
		foreach ($aStructures as $oStructure)
		{
			$result[] = $oStructure->indexing();
		}

		return $result;
	}

	/**
	 * Search callback function
	 * @param Search_Page_Model $oSearch_Page
	 * @return self
	 * @hostcms-event Structure_Module.searchCallback
	 */
	public function searchCallback($oSearch_Page)
	{
		if ($oSearch_Page->module_value_id)
		{
			$oStructure = Core_Entity::factory('Structure')->find($oSearch_Page->module_value_id);

			Core_Event::notify(get_class($this) . '.searchCallback', $this, array($oSearch_Page, $oStructure));

			!is_null($oStructure->id) && $oSearch_Page->addEntity($oStructure);
		}

		return $this;
	}

	/**
	 * Backend search callback function
	 * @param Search_Page_Model $oSearch_Page
	 * @return array 'href' and 'onclick'
	 */
	public function backendSearchCallback($oSearch_Page)
	{
		$href = $onclick = NULL;

		$iAdmin_Form_Id = 82;
		$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);
		$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form)->formSettings();

		$sPath = '/admin/structure/index.php';

		if ($oSearch_Page->module_value_id)
		{
			$oStructure = Core_Entity::factory('Structure')->find($oSearch_Page->module_value_id);

			if (!is_null($oStructure->id))
			{
				$href = $oAdmin_Form_Controller->getAdminActionLoadHref($sPath, 'edit', NULL, 0, $oStructure->id);
				$onclick = $oAdmin_Form_Controller->getAdminActionLoadAjax($sPath, 'edit', NULL, 0, $oStructure->id);
			}
		}

		return array(
			'icon' => 'fa-sitemap',
			'href' => $href,
			'onclick' => $onclick
		);
	}
}