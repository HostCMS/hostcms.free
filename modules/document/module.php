<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Documents Module.
 *
 * @package HostCMS
 * @subpackage Document
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Document_Module extends Core_Module
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
	public $date = '2022-11-01';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'document';

	/**
	 * Get Module's Menu
	 * @return array
	 */
	public function getMenu()
	{
		$this->menu = array(
			array(
				'sorting' => 20,
				'block' => 0,
				'ico' => 'fa fa-file-text-o',
				'name' => Core::_('Document.menu'),
				'href' => "/admin/document/index.php",
				'onclick' => "$.adminLoad({path: '/admin/document/index.php'}); return false"
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
	 * @hostcms-event Document_Module.indexing
	 */
	public function indexing($offset, $limit)
	{
		$offset = intval($offset);
		$limit = intval($limit);

		Core_Log::instance()->clear()
			->notify(FALSE)
			->status(Core_Log::$MESSAGE)
			->write("document indexing({$offset}, {$limit})");

		$oDocuments = Core_Entity::factory('Document');
		$oDocuments
			->queryBuilder()
			->orderBy('id', 'DESC')
			->limit($offset, $limit);

		Core_Event::notify(get_class($this) . '.indexing', $this, array($oDocuments));

		$aDocuments = $oDocuments->findAll(FALSE);

		$aPages = array();
		foreach ($aDocuments as $oDocument)
		{
			$aPages[] = $oDocument->indexing();
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

		$iAdmin_Form_Id = 9;
		$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);
		$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form)->formSettings();

		$sPath = '/admin/document/index.php';

		if ($oSearch_Page->module_value_id)
		{
			$oDocument = Core_Entity::factory('Document')->find($oSearch_Page->module_value_id);
			
			if (!is_null($oDocument->id))
			{
				$additionalParams = "document_dir_id={$oDocument->document_dir_id}";
				
				$href = $oAdmin_Form_Controller->getAdminActionLoadHref($sPath, 'edit', NULL, 1, $oDocument->id, $additionalParams);
				$onclick = $oAdmin_Form_Controller->getAdminActionLoadAjax($sPath, 'edit', NULL, 1, $oDocument->id, $additionalParams);
			}
		}

		return array(
			'icon' => 'fa-file-text-o',
			'href' => $href,
			'onclick' => $onclick
		);
	}
}