<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Information System Module.
 *
 * @package HostCMS
 * @subpackage Informationsystem
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Informationsystem_Module extends Core_Module
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
	public $date = '2020-08-04';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'informationsystem';

	/**
	 * List of Schedule Actions
	 * @var array
	 */
	protected $_scheduleActions = array(
		0 => 'searchIndexItem',
		1 => 'searchIndexGroup',
		2 => 'searchUnindexItem',
		3 => 'recountInformationsystem',
	);

	/**
	 * Get Module's Menu
	 * @return array
	 */
	public function getMenu()
	{
		$this->menu = array(
			array(
				'sorting' => 30,
				'block' => 0,
				'ico' => 'fa fa-newspaper-o',
				'name' => Core::_('Informationsystem.menu'),
				'href' => "/admin/informationsystem/index.php",
				'onclick' => "$.adminLoad({path: '/admin/informationsystem/index.php'}); return false"
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
	 */
	public function indexing($offset, $limit)
	{
		/**
		 * $_SESSION['search_block'] - номер блока индексации
		 */
		if (!isset($_SESSION['search_block']))
		{
			$_SESSION['search_block'] = 0;
		}

		if (!isset($_SESSION['last_limit']))
		{
			$_SESSION['last_limit'] = 0;
		}

		$limit_orig = $limit;

		$result = array();

		switch ($_SESSION['search_block'])
		{
			case 0:
				$aTmpResult = $this->indexingInformationsystemGroups($offset, $limit);

				$_SESSION['last_limit'] = count($aTmpResult);

				$result = array_merge($result, $aTmpResult);
				$count = count($result);

				if ($count < $limit_orig)
				{
					$_SESSION['search_block']++;
					$limit = $limit_orig - $count;
					$offset = 0;
				}
				else
				{
					return $result;
				}

			case 1:
				$aTmpResult = $this->indexingInformationsystemItems($offset, $limit);

				$_SESSION['last_limit'] = count($aTmpResult);

				$result = array_merge($result, $aTmpResult);
				$count = count($result);

				// Закончена индексация
				if ($count < $limit_orig)
				{
					$_SESSION['search_block']++;
					$limit = $limit_orig - $count;
					$offset = 0;
				}
				else
				{
					return $result;
				}
		}

		$_SESSION['search_block'] = 0;

		return $result;
	}

	/**
	 * Индексация информационных групп
	 *
	 * @param int $offset
	 * @param int $limit
	 * @return array
	 * @hostcms-event Informationsystem_Module.indexingInformationsystemGroups
	 */
	public function indexingInformationsystemGroups($offset, $limit)
	{
		$offset = intval($offset);
		$limit = intval($limit);

		$oInformationsystemGroup = Core_Entity::factory('Informationsystem_Group');
		$oInformationsystemGroup
			->queryBuilder()
			->straightJoin()
			->join('informationsystems', 'informationsystem_groups.informationsystem_id', '=', 'informationsystems.id')
			->join('structures', 'informationsystems.structure_id', '=', 'structures.id')
			->where('structures.active', '=', 1)
			->where('structures.indexing', '=', 1)
			->where('informationsystem_groups.indexing', '=', 1)
			->where('informationsystem_groups.shortcut_id', '=', 0)
			->where('informationsystem_groups.active', '=', 1)
			->where('informationsystem_groups.deleted', '=', 0)
			->where('informationsystems.deleted', '=', 0)
			->where('structures.deleted', '=', 0)
			->orderBy('informationsystem_groups.id', 'DESC')
			->limit($offset, $limit);

		Core_Event::notify(get_class($this) . '.indexingInformationsystemGroups', $this, array($oInformationsystemGroup));

		$aInformationsystemGroups = $oInformationsystemGroup->findAll(FALSE);

		$result = array();
		foreach ($aInformationsystemGroups as $oInformationsystemGroup)
		{
			$result[] = $oInformationsystemGroup->indexing();
		}

		return $result;
	}

	/**
	 * Индексация информационных элементов
	 *
	 * @param int $offset
	 * @param int $limit
	 * @return array
	 * @hostcms-event Informationsystem_Module.indexingInformationsystemItems
	 */
	public function indexingInformationsystemItems($offset, $limit)
	{
		$offset = intval($offset);
		$limit = intval($limit);

		$dateTime = Core_Date::timestamp2sql(time());

		$oInformationsystemItem = Core_Entity::factory('Informationsystem_Item');

		$oInformationsystemItem
			->queryBuilder()
			->straightJoin()
			->join('informationsystems', 'informationsystem_items.informationsystem_id', '=', 'informationsystems.id')
			->join('structures', 'informationsystems.structure_id', '=', 'structures.id')
			->leftJoin('informationsystem_groups', 'informationsystem_items.informationsystem_group_id', '=', 'informationsystem_groups.id')
			->where('structures.active', '=', 1)
			->where('structures.indexing', '=', 1)
			->where('informationsystem_items.indexing', '=', 1)
			->where('informationsystem_items.active', '=', 1)
			->where('informationsystem_items.shortcut_id', '=', 0)
			->where('informationsystem_items.deleted', '=', 0)
			->open()
				->where('informationsystem_items.start_datetime', '<', $dateTime)
				->setOr()
				->where('informationsystem_items.start_datetime', '=', '0000-00-00 00:00:00')
			->close()
			->setAnd()
			->open()
				->where('informationsystem_items.end_datetime', '>', $dateTime)
				->setOr()
				->where('informationsystem_items.end_datetime', '=', '0000-00-00 00:00:00')
			->close()
			->setAnd()
			->open()
				->where('informationsystem_groups.id', 'IS', NULL)
				->setOr()
				->where('informationsystem_groups.active', '=', 1)
				->where('informationsystem_groups.indexing', '=', 1)
			->close()
			->where('informationsystems.deleted', '=', 0)
			->where('structures.deleted', '=', 0)
			->orderBy('informationsystem_items.id', 'DESC')
			->limit($offset, $limit);

		Core_Event::notify(get_class($this) . '.indexingInformationsystemItems', $this, array($oInformationsystemItem));

		$aInformationsystemItems = $oInformationsystemItem->findAll(FALSE);

		$result = array();
		foreach ($aInformationsystemItems as $oInformationsystemItem)
		{
			$result[] = $oInformationsystemItem->indexing();
		}

		return $result;
	}

	/**
	 * Search callback function
	 * @param Search_Page_Model $oSearch_Page
	 * @return self
	 * @hostcms-event Informationsystem_Module.searchCallback
	 */
	public function searchCallback($oSearch_Page)
	{
		if ($oSearch_Page->module_value_id)
		{
			switch ($oSearch_Page->module_value_type)
			{
				case 1: // Информационые группы
					$oInformationsystem_Group = Core_Entity::factory('Informationsystem_Group')->find($oSearch_Page->module_value_id);

					Core_Event::notify(get_class($this) . '.searchCallback', $this, array($oSearch_Page, $oInformationsystem_Group));

					!is_null($oInformationsystem_Group->id) && $oSearch_Page->addEntity($oInformationsystem_Group);
				break;
				case 2: // Информационые элементы
					$oInformationsystem_Item = Core_Entity::factory('Informationsystem_Item')->find($oSearch_Page->module_value_id);

					if (!is_null($oInformationsystem_Item->id))
					{
						$oInformationsystem_Item
							->showXmlComments(TRUE)
							->showXmlProperties(TRUE);

						$oInformationsystem_Item->informationsystem_group_id
							&& $oSearch_Page->addEntity($oInformationsystem_Item->Informationsystem_Group);

						Core_Event::notify(get_class($this) . '.searchCallback', $this, array($oSearch_Page, $oInformationsystem_Item));

						$oSearch_Page->addEntity($oInformationsystem_Item);
					}
				break;
			}
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
		$href = $onclick = $icon = NULL;

		$iAdmin_Form_Id = 12;
		$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);
		$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form)->formSettings();

		$sPath = '/admin/informationsystem/item/index.php';

		if ($oSearch_Page->module_value_id)
		{
			switch ($oSearch_Page->module_value_type)
			{
				case 1: // Информационые группы
					$oInformationsystem_Group = Core_Entity::factory('Informationsystem_Group')->find($oSearch_Page->module_value_id);

					if (!is_null($oInformationsystem_Group->id))
					{
						$additionalParams = "informationsystem_id={$oInformationsystem_Group->Informationsystem->id}&informationsystem_group_id={$oInformationsystem_Group->id}";
						$href = $oAdmin_Form_Controller->getAdminLoadHref($sPath, NULL, NULL, $additionalParams);
						$onclick = $oAdmin_Form_Controller->getAdminLoadAjax($sPath, NULL, NULL, $additionalParams);
						$icon = "fa fa-folder-open-o";
					}
				break;
				case 2: // Информационые элементы
					$oInformationsystem_Item = Core_Entity::factory('Informationsystem_Item')->find($oSearch_Page->module_value_id);

					if (!is_null($oInformationsystem_Item->id))
					{
						$additionalParams = "informationsystem_id={$oInformationsystem_Item->Informationsystem->id}&informationsystem_group_id={$oInformationsystem_Item->informationsystem_group_id}";

						$href = $oAdmin_Form_Controller->getAdminActionLoadHref($sPath, 'edit', NULL, 1, $oInformationsystem_Item->id, $additionalParams);
						$onclick = $oAdmin_Form_Controller->getAdminActionLoadAjax($sPath, 'edit', NULL, 1, $oInformationsystem_Item->id, $additionalParams);
						$icon = "fa fa-file-text-o";
					}
				break;
			}
		}

		return array(
			'icon' => $icon,
			'href' => $href,
			'onclick' => $onclick
		);
	}

	/**
	 * Notify module on the action on schedule
	 * @param int $action action number
	 * @param int $entityId entity ID
	 * @return array
	 */
	public function callSchedule($action, $entityId)
	{
		if ($entityId)
		{
			switch ($action)
			{
				// Index item
				case 0:
					Core_Entity::factory('Informationsystem_Item', $entityId)->index()->clearCache();
				break;
				// Index group
				case 1:
					Core_Entity::factory('Informationsystem_Group', $entityId)->index()->clearCache();
				break;
				// Unindex item
				case 2:
					Core_Entity::factory('Informationsystem_Item', $entityId)->unindex()->clearCache();
				break;
				// Recount informationsystem
				case 3:
					Core_Entity::factory('Informationsystem', $entityId)->recount();
				break;
			}
		}
	}
}