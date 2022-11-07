<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Google sitemap
 * http://www.sitemaps.org/protocol.html
 *
 * - showInformationsystemGroups(TRUE|FALSE) выводить группы информационных систем, по умолчанию TRUE
 * - showInformationsystemItems(TRUE|FALSE) выводить элементы информационных систем, по умолчанию TRUE
 * - showInformationsystemTags(TRUE|FALSE) выводить метки информационных систем, по умолчанию FALSE
 * - showShopGroups(TRUE|FALSE) выводить группы магазина, по умолчанию TRUE
 * - showShopItems(TRUE|FALSE) выводить товары магазина, по умолчанию TRUE
 * - showModifications(TRUE|FALSE) выводить модификации товаров магазина, по умолчанию FALSE
 * - showShopTags(TRUE|FALSE) выводить метки товаров магазина, по умолчанию FALSE
 * - showShopFilter(TRUE|FALSE) выводить SEO-фильтр магазина, по умолчанию TRUE
 * - rebuildTime время в секундах, которое должно пройти с момента создания sitemap.xml для его перегенерации. По умолчанию 14400
 * - limit ограничение на единичную выборку элементов, по умолчанию 1000. При наличии достаточного объема памяти рекомендуется увеличить параметр
 * - createIndex(TRUE|FALSE) разбивать карту на несколько файлов, по умолчанию FALSE
 * - perFile Count of nodes per one file
 * - defaultProtocol('http://') протокол по умолчанию, устанавливается в зависимоти от опции https у сайта
 * - urlset(array('xmlns' => 'http://www.sitemaps.org/schemas/sitemap/0.9')) массив опций для urlset
 * - fileName() схема построения имени файла, по умолчанию 'sitemap-%d.xml'
 * - multipleFileName() схема построения имени файла внутри индекса, по умолчанию 'sitemap-%d-%d.xml'
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Sitemap extends Core_Servant_Properties
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'showInformationsystemGroups',
		'showInformationsystemItems',
		'showInformationsystemTags',
		'showShopGroups',
		'showShopItems',
		'showModifications',
		'showShopTags',
		'showShopFilter',
		'rebuildTime',
		'defaultProtocol',
		'urlset',
		'limit',
		'createIndex',
		'perFile',
		'fileName',
		'multipleFileName',
	);

	/**
	 * Site
	 * @var Site_Model
	 */
	protected $_oSite = NULL;

	/**
	 * Constructor
	 * @param Site_Model $oSite Site object
	 */
	public function __construct(Site_Model $oSite)
	{
		parent::__construct();

		if ((!defined('DENY_INI_SET') || !DENY_INI_SET) && strpos(@ini_get('disable_functions'), 'set_time_limit') === FALSE)
		{
			@set_time_limit(21600);
			ini_set('max_execution_time', '21600');
		}

		$this->_oSite = $oSite;

		$this->_aSiteuserGroups = array(0, -1);

		/*if (Core::moduleIsActive('siteuser'))
		{
			$oSiteuser = Core_Entity::factory('Siteuser')->getCurrent();

			if ($oSiteuser)
			{
				$aSiteuser_Groups = $oSiteuser->Siteuser_Groups->findAll(FALSE);
				foreach ($aSiteuser_Groups as $oSiteuser_Group)
				{
					$this->_aSiteuserGroups[] = $oSiteuser_Group->id;
				}
			}
		}*/

		$this->rebuildTime = 14400; // 4 часа
		$this->limit = 1000;

		$this->showInformationsystemTags = $this->showModifications = $this->showShopTags
			= $this->createIndex = FALSE;

		$this->showInformationsystemGroups = $this->showInformationsystemItems
			= $this->showShopGroups = $this->showShopItems
			= $this->showShopFilter = TRUE;

		$this->_Informationsystems = $this->_Shops = array();

		$this->urlset = array('xmlns' => 'http://www.sitemaps.org/schemas/sitemap/0.9');

		$this->defaultProtocol = $this->_oSite->https
			? 'https://'
			: 'http://';

		$this->fileName = 'sitemap-%d.xml';
		$this->multipleFileName = 'sitemap-%d-%d.xml';
	}

	/**
	 * List of user groups
	 * @var array
	 */
	protected $_aSiteuserGroups = NULL;

	/**
	 * List of information systems
	 * @var array
	 */
	protected $_Informationsystems = array();

	/**
	 * List of shops
	 * @var array
	 */
	protected $_Shops = array();

	/**
	 * Get site
	 * @return Site_Model
	 */
	public function getSite()
	{
		return $this->_oSite;
	}

	/**
	 * Select Structure_Models by parent_id
	 * @param int $structure_id structure ID
	 * @return array
	 * @hostcms-event Core_Sitemap.onBeforeSelectStructures
	 */
	protected function _selectStructuresByParentId($structure_id)
	{
		$oSite = $this->getSite();

		$oStructures = $oSite->Structures;
		$oStructures
			->queryBuilder()
			->where('structures.parent_id', '=', $structure_id)
			->where('structures.active', '=', 1)
			->where('structures.indexing', '=', 1)
			->where('structures.siteuser_group_id', 'IN', $this->_aSiteuserGroups)
			->orderBy('sorting')
			->orderBy('name');

		Core_Event::notify('Core_Sitemap.onBeforeSelectStructures', $this, array($oStructures));

		$aStructure = $oStructures->findAll(FALSE);

		return $aStructure;
	}

	/**
	 * Current Site Alias
	 * @var mixed
	 */
	protected $_siteAlias = NULL;

	/**
	 * Executes the business logic.
	 * @return self
	 * @hostcms-event Core_Sitemap.onBeforeExecute
	 */
	public function execute()
	{
		Core_Event::notify('Core_Sitemap.onBeforeExecute', $this);

		$this->_close();

		$sIndexFilePath = $this->_getIndexFilePath();

		if ($this->createIndex)
		{
			$this->createSitemapDir();

			if ($this->_bRebuild)
			{
				$sIndex = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";

				$sProtocol = $this->_oSite->https
					? 'https://'
					: $this->defaultProtocol;

				$sIndex .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
				foreach ($this->_aIndexedFiles as $filename)
				{
					$sIndex .= "<sitemap>\n";
					$sIndex .= "<loc>{$sProtocol}{$this->_siteAlias}{$this->getSitemapHref()}{$filename}</loc>\n";
					$sIndex .= "<lastmod>" . date('Y-m-d') . "</lastmod>\n";
					$sIndex .= "</sitemap>\n";
				}

				$sIndex .= '</sitemapindex>';

				echo $sIndex;

				Core_File::write($sIndexFilePath, $sIndex);
			}
		}

		if (!$this->_bRebuild)
		{
			echo Core_File::read($sIndexFilePath);
		}

		return $this;
	}

	/**
	 * Get Structure Protocol
	 *
	 * @param Structure_Model $oStructure
	 * @return string
	 */
	public function getProtocol($oStructure)
	{
		return $oStructure->https
			? 'https://'
			: $this->defaultProtocol;
	}

	/**
	 * Add structure nodes by parent
	 * @param int $structure_id structure ID
	 * @return self
	 * @hostcms-event Core_Sitemap.onBeforeAddStructure
	 */
	protected function _structure($structure_id = 0)
	{
		$oSite = $this->getSite();

		$aStructure = $this->_selectStructuresByParentId($structure_id);

		$dateTime = Core_Date::timestamp2sql(time());

		foreach ($aStructure as $oStructure)
		{
			$sProtocol = $this->getProtocol($oStructure);

			$loc = $sProtocol . $this->_siteAlias . $oStructure->getPath();
			$changefreq = $oStructure->changefreq;
			$priority = $oStructure->priority;
			$entity = $oStructure;

			Core_Event::notify('Core_Sitemap.onBeforeAddStructure', $this, array($loc, $changefreq, $priority, $entity));

			$lastReturn = Core_Event::getLastReturn();
			if (is_array($lastReturn) && count($lastReturn) == 4)
			{
				list($loc, $changefreq, $priority, $entity) = $lastReturn;
			}

			$this->addNode($loc, $changefreq, $priority, $entity);

			// Informationsystem
			if ($this->showInformationsystemGroups && isset($this->_Informationsystems[$oStructure->id]) && Core::moduleIsActive('informationsystem'))
			{
				$oInformationsystem = $this->_Informationsystems[$oStructure->id];

				$this->_fillInformationsystem($oStructure, $oInformationsystem);
			}

			// Shop
			if ($this->showShopGroups && isset($this->_Shops[$oStructure->id]) && Core::moduleIsActive('shop'))
			{
				$oShop = $this->_Shops[$oStructure->id];

				$this->_fillShop($oStructure, $oShop);
			}

			// Structure
			$this->_structure($oStructure->id);
		}

		return $this;
	}

	/**
	 * Add Informationsystem Nodes
	 *
	 * @param Structure_Model $oStructure
	 * @param Informationsystem_Model $oInformationsystem
	 * @return self
	 * @hostcms-event Core_Sitemap.onBeforeSelectInformationsystemGroups
	 * @hostcms-event Core_Sitemap.onBeforeAddInformationsystemGroup
	 * @hostcms-event Core_Sitemap.onBeforeSelectInformationsystemItems
	 * @hostcms-event Core_Sitemap.onBeforeAddInformationsystemItem
	 * @hostcms-event Core_Sitemap.onBeforeSelectInformationsystemTags
	 * @hostcms-event Core_Sitemap.onBeforeAddInformationsystemTag
	 */
	protected function _fillInformationsystem(Structure_Model $oStructure, Informationsystem_Model $oInformationsystem)
	{
		$oCore_QueryBuilder_Select = Core_QueryBuilder::select(array('MAX(id)', 'max_id'));
		$oCore_QueryBuilder_Select
			->from('informationsystem_groups')
			->where('informationsystem_groups.informationsystem_id', '=', $oInformationsystem->id)
			->where('informationsystem_groups.shortcut_id', '=', 0)
			->where('informationsystem_groups.deleted', '=', 0);

		$oDataBase = $oCore_QueryBuilder_Select->execute();

		$aRow = $oDataBase->asAssoc()->current();

		$oDataBase->free();

		$maxId = $aRow['max_id'];

		$iFrom = 0;

		$aGroupsIDs = array();

		$sProtocol = $this->getProtocol($oStructure);

		$path = $sProtocol . $this->_siteAlias . $oInformationsystem->Structure->getPath();

		$dateTime = Core_Date::timestamp2sql(time());

		do {
			$oInformationsystem_Groups = $oInformationsystem->Informationsystem_Groups;
			$oInformationsystem_Groups->queryBuilder()
				->select('informationsystem_groups.id',
					'informationsystem_groups.informationsystem_id',
					'informationsystem_groups.parent_id',
					'informationsystem_groups.path'
				)
				->where('informationsystem_groups.id', 'BETWEEN', array($iFrom + 1, $iFrom + $this->limit))
				->where('informationsystem_groups.siteuser_group_id', 'IN', $this->_aSiteuserGroups)
				->where('informationsystem_groups.active', '=', 1)
				->where('informationsystem_groups.indexing', '=', 1)
				->where('informationsystem_groups.shortcut_id', '=', 0);

			Core_Event::notify('Core_Sitemap.onBeforeSelectInformationsystemGroups', $this, array($oInformationsystem_Groups));

			$aInformationsystem_Groups = $oInformationsystem_Groups->findAll(FALSE);

			foreach ($aInformationsystem_Groups as $oInformationsystem_Group)
			{
				$aGroupsIDs[$oInformationsystem_Group->id] = $oInformationsystem_Group->id;

				$loc = $path . $oInformationsystem_Group->getPath();
				$changefreq = $oStructure->changefreq;
				$priority = $oStructure->priority;
				$entity = $oInformationsystem_Group;

				Core_Event::notify('Core_Sitemap.onBeforeAddInformationsystemGroup', $this, array($loc, $changefreq, $priority, $entity));

				$lastReturn = Core_Event::getLastReturn();
				if (is_array($lastReturn) && count($lastReturn) == 4)
				{
					list($loc, $changefreq, $priority, $entity) = $lastReturn;
				}

				$this->addNode($loc, $changefreq, $priority, $entity);
			}
			$iFrom += $this->limit;
		}
		while ($iFrom < $maxId);

		// Informationsystem's items
		if ($this->showInformationsystemItems)
		{
			$oCore_QueryBuilder_Select = Core_QueryBuilder::select(array('MAX(id)', 'max_id'));
			$oCore_QueryBuilder_Select
				->from('informationsystem_items')
				->where('informationsystem_items.informationsystem_id', '=', $oInformationsystem->id)
				->where('informationsystem_items.deleted', '=', 0);

			$oDataBase = $oCore_QueryBuilder_Select->execute();

			$aRow = $oDataBase->asAssoc()->current();

			$oDataBase->free();

			$maxId = $aRow['max_id'];

			$iFrom = 0;

			do {
				$oInformationsystem_Items = $oInformationsystem->Informationsystem_Items;
				$oInformationsystem_Items->queryBuilder()
					->select('informationsystem_items.id',
						'informationsystem_items.informationsystem_id',
						'informationsystem_items.informationsystem_group_id',
						'informationsystem_items.shortcut_id',
						'informationsystem_items.path'
					)
					->leftJoin('informationsystem_groups', 'informationsystem_groups.id', '=', 'informationsystem_items.informationsystem_group_id')
					->where('informationsystem_items.id', 'BETWEEN', array($iFrom + 1, $iFrom + $this->limit))
					// Активность группы или группа корневая
					->open()
						->where('informationsystem_groups.active', '=', 1)
						->where('informationsystem_groups.deleted', '=', 0)
						->where('informationsystem_groups.siteuser_group_id', 'IN', $this->_aSiteuserGroups)
						->setOr()
						->where('informationsystem_groups.id', 'IS', NULL)
					->close()
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
					->where('informationsystem_items.siteuser_group_id', 'IN', $this->_aSiteuserGroups)
					->where('informationsystem_items.active', '=', 1)
					->where('informationsystem_items.indexing', '=', 1)
					->where('informationsystem_items.closed', '=', 0)
					->where('informationsystem_items.shortcut_id', '=', 0);

				Core_Event::notify('Core_Sitemap.onBeforeSelectInformationsystemItems', $this, array($oInformationsystem_Items));

				$aInformationsystem_Items = $oInformationsystem_Items->findAll(FALSE);
				foreach ($aInformationsystem_Items as $oInformationsystem_Item)
				{
					if ($oInformationsystem_Item->informationsystem_group_id == 0
						|| isset($aGroupsIDs[$oInformationsystem_Item->informationsystem_group_id]))
					{
						$loc = $path . $oInformationsystem_Item->getPath();
						$changefreq = $oStructure->changefreq;
						$priority = $oStructure->priority;
						$entity = $oInformationsystem_Item;

						Core_Event::notify('Core_Sitemap.onBeforeAddInformationsystemItem', $this, array($loc, $changefreq, $priority, $entity));

						$lastReturn = Core_Event::getLastReturn();
						if (is_array($lastReturn) && count($lastReturn) == 4)
						{
							list($loc, $changefreq, $priority, $entity) = $lastReturn;
						}

						$this->addNode($loc, $changefreq, $priority, $entity);
					}
				}

				$iFrom += $this->limit;
			}
			while ($iFrom < $maxId);
		}

		unset($aGroupsIDs);

		// Tags
		if ($this->showInformationsystemTags && Core::moduleIsActive('tag'))
		{
			$oCore_QueryBuilder_Select = Core_QueryBuilder::select(array('MAX(id)', 'max_id'));
			$oCore_QueryBuilder_Select
				->from('tags')
				->where('tags.deleted', '=', 0);

			$oDataBase = $oCore_QueryBuilder_Select->execute();

			$aRow = $oDataBase->asAssoc()->current();

			$oDataBase->free();

			$maxId = $aRow['max_id'];

			$iFrom = 0;

			do {
				$oTags = Core_Entity::factory('Tag');

				$oTags->queryBuilder()
					->select(array('COUNT(tag_id)', 'count'), 'tags.*')
					->where('tags.id', 'BETWEEN', array($iFrom + 1, $iFrom + $this->limit))
					->join('tag_informationsystem_items', 'tag_informationsystem_items.tag_id', '=', 'tags.id')
					->join('informationsystem_items', 'tag_informationsystem_items.informationsystem_item_id', '=', 'informationsystem_items.id')
					->leftJoin('informationsystem_groups', 'informationsystem_groups.id', '=', 'informationsystem_items.informationsystem_group_id')
					// Активность группы или группа корневая
					->open()
						->where('informationsystem_groups.active', '=', 1)
						->where('informationsystem_groups.deleted', '=', 0)
						->where('informationsystem_groups.siteuser_group_id', 'IN', $this->_aSiteuserGroups)
						->setOr()
						->where('informationsystem_groups.id', 'IS', NULL)
					->close()
					->where('informationsystem_items.siteuser_group_id', 'IN', $this->_aSiteuserGroups)
					->where('informationsystem_items.informationsystem_id', '=', $oInformationsystem->id)
					->where('informationsystem_items.deleted', '=', 0)
					->groupBy('tag_informationsystem_items.tag_id')
					->having('count', '>', 0);

				Core_Event::notify('Core_Sitemap.onBeforeSelectInformationsystemTags', $this, array($oTags));

				$aTags = $oTags->findAll(FALSE);
				foreach ($aTags as $oTag)
				{
					$loc = $path . 'tag/' . rawurlencode($oTag->path) . '/';
					$changefreq = $oStructure->changefreq;
					$priority = $oStructure->priority;
					$entity = $oTag;

					Core_Event::notify('Core_Sitemap.onBeforeAddInformationsystemTag', $this, array($loc, $changefreq, $priority, $entity));

					$lastReturn = Core_Event::getLastReturn();
					if (is_array($lastReturn) && count($lastReturn) == 4)
					{
						list($loc, $changefreq, $priority, $entity) = $lastReturn;
					}

					$this->addNode($loc, $changefreq, $priority, $entity);
				}

				$iFrom += $this->limit;
			}
			while ($iFrom < $maxId);
		}

		return $this;
	}

	/**
	 * Add Shop Nodes
	 *
	 * @param Structure_Model $oStructure
	 * @param Shop_Model $oInformationsystem
	 * @return self
	 * @hostcms-event Core_Sitemap.onBeforeSelectShopGroups
	 * @hostcms-event Core_Sitemap.onBeforeAddShopGroup
	 * @hostcms-event Core_Sitemap.onBeforeSelectShopItems
	 * @hostcms-event Core_Sitemap.onBeforeAddShopItem
	 * @hostcms-event Core_Sitemap.onBeforeSelectShopTags
	 * @hostcms-event Core_Sitemap.onBeforeAddShopTag
	 * @hostcms-event Core_Sitemap.onBeforeAddShopFilter
	 */
	protected function _fillShop(Structure_Model $oStructure, Shop_Model $oShop)
	{
		$oCore_QueryBuilder_Select = Core_QueryBuilder::select(array('MAX(id)', 'max_id'));
		$oCore_QueryBuilder_Select
			->from('shop_groups')
			->where('shop_groups.shop_id', '=', $oShop->id)
			->where('shop_groups.shortcut_id', '=', 0)
			->where('shop_groups.deleted', '=', 0);

		$oDataBase = $oCore_QueryBuilder_Select->execute();

		$aRow = $oDataBase->asAssoc()->current();

		$oDataBase->free();

		$maxId = $aRow['max_id'];

		$iFrom = 0;

		$aGroupsIDs = array();

		$sProtocol = $this->getProtocol($oStructure);

		$path = $sProtocol . $this->_siteAlias . $oShop->Structure->getPath();

		$dateTime = Core_Date::timestamp2sql(time());

		do {
			$oShop_Groups = $oShop->Shop_Groups;
			$oShop_Groups->queryBuilder()
				->select('shop_groups.id',
					'shop_groups.shop_id',
					'shop_groups.parent_id',
					'shop_groups.path'
				)
				->where('shop_groups.id', 'BETWEEN', array($iFrom + 1, $iFrom + $this->limit))
				->where('shop_groups.siteuser_group_id', 'IN', $this->_aSiteuserGroups)
				->where('shop_groups.shortcut_id', '=', 0)
				->where('shop_groups.active', '=', 1)
				->where('shop_groups.indexing', '=', 1);

			Core_Event::notify('Core_Sitemap.onBeforeSelectShopGroups', $this, array($oShop_Groups));

			$aShop_Groups = $oShop_Groups->findAll(FALSE);

			foreach ($aShop_Groups as $oShop_Group)
			{
				$aGroupsIDs[$oShop_Group->id] = $oShop_Group->id;

				$loc = $path . $oShop_Group->getPath();
				$changefreq = $oStructure->changefreq;
				$priority = $oStructure->priority;
				$entity = $oShop_Group;

				Core_Event::notify('Core_Sitemap.onBeforeAddShopGroup', $this, array($loc, $changefreq, $priority, $entity));

				$lastReturn = Core_Event::getLastReturn();
				if (is_array($lastReturn) && count($lastReturn) == 4)
				{
					list($loc, $changefreq, $priority, $entity) = $lastReturn;
				}

				$this->addNode($loc, $changefreq, $priority, $entity);
			}

			$iFrom += $this->limit;
		}
		while ($iFrom < $maxId);

		// Shop's items
		if ($this->showShopItems)
		{
			$oCore_QueryBuilder_Select = Core_QueryBuilder::select(array('MAX(id)', 'max_id'));
			$oCore_QueryBuilder_Select
				->from('shop_items')
				->where('shop_items.shop_id', '=', $oShop->id)
				->where('shop_items.deleted', '=', 0);

			$oDataBase = $oCore_QueryBuilder_Select->execute();

			$aRow = $oDataBase->asAssoc()->current();

			$oDataBase->free();

			$maxId = $aRow['max_id'];

			$iFrom = 0;

			do {
				$oShop_Items = $oShop->Shop_Items;
				$oShop_Items->queryBuilder()
					->select('shop_items.id',
						'shop_items.shop_id',
						'shop_items.shop_group_id',
						'shop_items.shortcut_id',
						'shop_items.modification_id',
						'shop_items.path'
					)
					->leftJoin('shop_groups', 'shop_groups.id', '=', 'shop_items.shop_group_id')
					->where('shop_items.id', 'BETWEEN', array($iFrom + 1, $iFrom + $this->limit))
					// Активность группы или группа корневая
					->open()
						->where('shop_groups.active', '=', 1)
						->where('shop_groups.deleted', '=', 0)
						->where('shop_groups.siteuser_group_id', 'IN', $this->_aSiteuserGroups)
						->setOr()
						->where('shop_groups.id', 'IS', NULL)
					->close()
					->open()
						->where('shop_items.start_datetime', '<', $dateTime)
						->setOr()
						->where('shop_items.start_datetime', '=', '0000-00-00 00:00:00')
					->close()
					->setAnd()
					->open()
						->where('shop_items.end_datetime', '>', $dateTime)
						->setOr()
						->where('shop_items.end_datetime', '=', '0000-00-00 00:00:00')
					->close()
					->where('shop_items.siteuser_group_id', 'IN', $this->_aSiteuserGroups)
					->where('shop_items.active', '=', 1)
					->where('shop_items.indexing', '=', 1)
					->where('shop_items.shortcut_id', '=', 0);

				// Modifications
				!$this->showModifications
					&& $oShop_Items->queryBuilder()->where('shop_items.modification_id', '=', 0);

				Core_Event::notify('Core_Sitemap.onBeforeSelectShopItems', $this, array($oShop_Items));

				$aShop_Items = $oShop_Items->findAll(FALSE);
				foreach ($aShop_Items as $oShop_Item)
				{
					if ($oShop_Item->shop_group_id == 0
						|| isset($aGroupsIDs[$oShop_Item->shop_group_id]))
					{
						$loc = $path . $oShop_Item->getPath();
						$changefreq = $oStructure->changefreq;
						$priority = $oStructure->priority;
						$entity = $oShop_Item;

						Core_Event::notify('Core_Sitemap.onBeforeAddShopItem', $this, array($loc, $changefreq, $priority, $entity));

						$lastReturn = Core_Event::getLastReturn();
						if (is_array($lastReturn) && count($lastReturn) == 4)
						{
							list($loc, $changefreq, $priority, $entity) = $lastReturn;
						}

						$this->addNode($loc, $changefreq, $priority, $entity);
					}
				}

				$iFrom += $this->limit;
			}
			while ($iFrom < $maxId);
		}

		unset($aGroupsIDs);

		// Tags
		if ($this->showShopTags && Core::moduleIsActive('tag'))
		{
			$oCore_QueryBuilder_Select = Core_QueryBuilder::select(array('MAX(id)', 'max_id'));
			$oCore_QueryBuilder_Select
				->from('tags')
				->where('tags.deleted', '=', 0);

			$oDataBase = $oCore_QueryBuilder_Select->execute();

			$aRow = $oDataBase->asAssoc()->current();

			$oDataBase->free();

			$maxId = $aRow['max_id'];

			$iFrom = 0;

			do {
				$oTags = Core_Entity::factory('Tag');

				$oTags->queryBuilder()
					->select(array('COUNT(tag_id)', 'count'), 'tags.*')
					->where('tags.id', 'BETWEEN', array($iFrom + 1, $iFrom + $this->limit))
					->join('tag_shop_items', 'tag_shop_items.tag_id', '=', 'tags.id')
					->join('shop_items', 'tag_shop_items.shop_item_id', '=', 'shop_items.id')
					->leftJoin('shop_groups', 'shop_groups.id', '=', 'shop_items.shop_group_id')
					// Активность группы или группа корневая
					->open()
						->where('shop_groups.active', '=', 1)
						->where('shop_groups.deleted', '=', 0)
						->where('shop_groups.siteuser_group_id', 'IN', $this->_aSiteuserGroups)
						->setOr()
						->where('shop_groups.id', 'IS', NULL)
					->close()
					->where('shop_items.siteuser_group_id', 'IN', $this->_aSiteuserGroups)
					->where('shop_items.shop_id', '=', $oShop->id)
					->where('shop_items.deleted', '=', 0)
					->groupBy('tag_shop_items.tag_id')
					->having('count', '>', 0);

				Core_Event::notify('Core_Sitemap.onBeforeSelectShopTags', $this, array($oTags));

				$aTags = $oTags->findAll(FALSE);
				foreach ($aTags as $oTag)
				{
					$loc = $path . 'tag/' . rawurlencode($oTag->path) . '/';
					$changefreq = $oStructure->changefreq;
					$priority = $oStructure->priority;
					$entity = $oTag;

					Core_Event::notify('Core_Sitemap.onBeforeAddShopTag', $this, array($loc, $changefreq, $priority, $entity));

					$lastReturn = Core_Event::getLastReturn();
					if (is_array($lastReturn) && count($lastReturn) == 4)
					{
						list($loc, $changefreq, $priority, $entity) = $lastReturn;
					}

					$this->addNode($loc, $changefreq, $priority, $entity);
				}

				$iFrom += $this->limit;
			}
			while ($iFrom < $maxId);
		}

		if ($this->showShopFilter)
		{
			$oCore_QueryBuilder_Select = Core_QueryBuilder::select(array('MAX(id)', 'max_id'));
			$oCore_QueryBuilder_Select
				->from('shop_filter_seos')
				->where('shop_filter_seos.shop_id', '=', $oShop->id)
				->where('shop_filter_seos.deleted', '=', 0);

			$oDataBase = $oCore_QueryBuilder_Select->execute();

			$aRow = $oDataBase->asAssoc()->current();

			$oDataBase->free();

			$maxId = $aRow['max_id'];

			$iFrom = 0;

			do {
				$oShop_Filter_Seos = $oShop->Shop_Filter_Seos;
				$oShop_Filter_Seos->queryBuilder()
					->where('shop_filter_seos.id', 'BETWEEN', array($iFrom + 1, $iFrom + $this->limit))
					->where('shop_filter_seos.active', '=', 1)
					->where('shop_filter_seos.indexing', '=', 1);

				$aShop_Filter_Seos = $oShop_Filter_Seos->findAll(FALSE);

				foreach ($aShop_Filter_Seos as $oShop_Filter_Seo)
				{
					$loc = $path . $oShop_Filter_Seo->getUrl();
					$changefreq = $oStructure->changefreq;
					$priority = $oStructure->priority;
					$entity = $oShop_Filter_Seo;

					Core_Event::notify('Core_Sitemap.onBeforeAddShopFilter', $this, array($loc, $changefreq, $priority, $entity));

					$lastReturn = Core_Event::getLastReturn();
					if (is_array($lastReturn) && count($lastReturn) == 4)
					{
						list($loc, $changefreq, $priority, $entity) = $lastReturn;
					}

					$this->addNode($loc, $changefreq, $priority, $entity);
				}

				$iFrom += $this->limit;
			}
			while ($iFrom < $maxId);
		}

		return $this;
	}

	/**
	 * Is it necessary to rebuild sitemap?
	 * @var boolean
	 */
	protected $_bRebuild = TRUE;

	/**
	 * Add Informationsystem
	 * @param int $structure_id
	 * @param Informationsystem_Model $oInformationsystem
	 */
	public function addInformationsystem($structure_id, Informationsystem_Model $oInformationsystem)
	{
		$this->_Informationsystems[$structure_id] = $oInformationsystem;
		return $this;
	}

	/**
	 * Add Shop
	 * @param int $structure_id
	 * @param Shop_Model $oShop
	 */
	public function addShop($structure_id, Shop_Model $oShop)
	{
		$this->_Shops[$structure_id] = $oShop;
		return $this;
	}

	/**
	 * Fill nodes of structure
	 * @return self
	 * @hostcms-event Core_Sitemap.onBeforeFillNodeInformationsystems
	 * @hostcms-event Core_Sitemap.onAfterFillNodeInformationsystems
	 * @hostcms-event Core_Sitemap.onBeforeFillNodeShops
	 * @hostcms-event Core_Sitemap.onAfterFillNodeShops
	 */
	public function fillNodes()
	{
		$oSite_Alias = $this->_oSite->getCurrentAlias();

		$this->_siteAlias = $oSite_Alias->name;

		$sIndexFilePath = $this->_getIndexFilePath();

		$this->_bRebuild = !is_file($sIndexFilePath) || time() > filemtime($sIndexFilePath) + $this->rebuildTime;

		if ($this->_bRebuild)
		{
			$this->createIndex && $this->createSitemapDir();

			$oSite = $this->getSite();

			if (($this->showInformationsystemGroups || $this->showInformationsystemItems) && Core::moduleIsActive('informationsystem'))
			{
				Core_Event::notify('Core_Sitemap.onBeforeFillNodeInformationsystems', $this);

				$aInformationsystems = $oSite->Informationsystems->findAll();
				foreach ($aInformationsystems as $oInformationsystem)
				{
					$this->addInformationsystem($oInformationsystem->structure_id, $oInformationsystem);
				}

				Core_Event::notify('Core_Sitemap.onAfterFillNodeInformationsystems', $this);
			}

			if (($this->showShopGroups || $this->showShopItems) && Core::moduleIsActive('shop'))
			{
				Core_Event::notify('Core_Sitemap.onBeforeFillNodeShops', $this);

				$aShops = $oSite->Shops->findAll();
				foreach ($aShops as $oShop)
				{
					$this->addShop($oShop->structure_id, $oShop);
				}

				Core_Event::notify('Core_Sitemap.onAfterFillNodeShops', $this);
			}

			$this->_structure(0);
		}

		return $this;
	}

	/**
	 * List of sitemap files
	 * @var array
	 */
	protected $_aIndexedFiles = array();

	/**
	 * Current output file
	 * @var Core_Out_File
	 */
	protected $_currentOut = NULL;

	/**
	 * Get current output file
	 * @return Core_Out_File
	 */
	protected function _getOut()
	{
		if ($this->createIndex)
		{
			if (is_null($this->_currentOut) || $this->_inFile >= $this->perFile)
			{
				$this->_getNewOutFile();
			}
		}
		elseif (is_null($this->_currentOut))
		{
			$this->_currentOut = new Core_Out_Std();
			$this->_open();
		}

		return $this->_currentOut;
	}

	/**
	 * Count URL in current file
	 * @var int
	 */
	protected $_inFile = 0;

	/**
	 * Sitemap files count
	 * @var int
	 */
	protected $_countFile = 1;

	/**
	 * Open current output file
	 * @return self
	 */
	protected function _open()
	{
		$aUrlset = array();
		foreach ($this->urlset as $key => $value)
		{
			$aUrlset[] = $key . '="' . htmlspecialchars($value) . '"';
		}

		$this->_currentOut->open();
		$this->_currentOut->write('<?xml version="1.0" encoding="UTF-8"?>' . "\n")
			->write('<urlset ' . implode(' ', $aUrlset) . '>' . "\n");
		return $this;
	}

	/**
	 * Close current output file
	 * @return self
	 */
	protected function _close()
	{
		if ($this->_currentOut)
		{
			$this->_currentOut->write("</urlset>\n");
			$this->_currentOut->close();
		}
		return $this;
	}

	/**
	 * Get new file for sitemap
	 */
	protected function _getNewOutFile()
	{
		if (!is_null($this->_currentOut))
		{
			$this->_close();

			$this->_countFile++;
			$this->_inFile = 0;
		}

		$this->_aIndexedFiles[] = $filename = sprintf($this->multipleFileName, $this->_oSite->id, $this->_countFile);

		$this->_currentOut = new Core_Out_File();
		//$this->_currentOut->filePath(CMS_FOLDER . $filename);
		$this->_currentOut->filePath($this->getSitemapDir() . $filename);
		$this->_open();
	}

	/**
	 * Add node to sitemap
	 * @param string $loc location
	 * @param int $changefreq change frequency
	 * @param float $priority priority
	 * @param Core_Entity|NULL $entity Core_Entity, e.g. Structure_Model or Shop_Item_Model
	 * @return self
	 * @hostcms-event Core_Sitemap.onBeforeAddNode
	 * @hostcms-event Core_Sitemap.onAfterAddNode
	 */
	public function addNode($loc, $changefreq, $priority, $entity = NULL)
	{
		switch ($changefreq)
		{
			case 0 : $sChangefreq = 'always'; break;
			case 1 : $sChangefreq = 'hourly'; break;
			default:
			case 2 : $sChangefreq = 'daily'; break;
			case 3 : $sChangefreq = 'weekly'; break;
			case 4 : $sChangefreq = 'monthly'; break;
			case 5 : $sChangefreq = 'yearly'; break;
			case 6 : $sChangefreq = 'never'; break;
		}

		$content = "<url>\n";

		Core_Event::notify('Core_Sitemap.onBeforeAddNode', $this, array($entity));
		$content .= Core_Event::getLastReturn();

		$content .= "<loc>{$loc}</loc>\n" .
		"<changefreq>" . $sChangefreq . "</changefreq>\n" .
		"<priority>" . $priority . "</priority>\n";

		Core_Event::notify('Core_Sitemap.onAfterAddNode', $this, array($entity));
		$content .= Core_Event::getLastReturn();

		$content .= "</url>\n";

		$this->_getOut()->write($content);

		$this->_inFile++;

		return $this;
	}

	/**
	 * Get index file path
	 * @return string
	 */
	protected function _getIndexFilePath()
	{
		return CMS_FOLDER . Core::$mainConfig['sitemapDirectory'] . sprintf($this->fileName, $this->_oSite->id);
	}

	/**
	 * Create sitemap dir
	 * @return self
	 */
	public function createSitemapDir()
	{
		clearstatcache();

		$sSitemapDir = $this->getSitemapDir();
		!is_dir($sSitemapDir) && Core_File::mkdir($sSitemapDir);

		return $this;
	}

	/**
	 * Get sitemap dir
	 * @return string
	 */
	public function getSitemapDir()
	{
		return CMS_FOLDER . Core::$mainConfig['sitemapDirectory'];
	}

	/**
	 * Get sitemap href
	 * @return string
	 */
	public function getSitemapHref()
	{
		return '/' . Core::$mainConfig['sitemapDirectory'];
	}
}