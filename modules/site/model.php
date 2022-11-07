<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Site_Model
 *
 * @package HostCMS
 * @subpackage Site
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Site_Model extends Core_Entity
{
	/**
	 * Backend property
	 * @var int
	 */
	public $img = 1;

	/**
	 * Backend property
	 * @var mixed
	 */
	public $rollback = 0;

	/**
	 * Backend property
	 * @var int
	 * @var int
	 */
	public $domains = 1;

	/**
	 * Forbidden tags. If list of tags is empty, all tags will show.
	 * @var array
	 */
	protected $_forbiddenTags = array(
		'deleted',
		'user_id',
		'robots',
		'key',
		'error',
		'error404',
		'error403',
		'closed',
		'safe_email',
		'css_left',
		'css_right',
		'notes',
		'uploaddir',
		'nesting_level',
		'html_cache_use',
		'html_cache_with',
		'html_cache_without',
		'html_cache_clear_probability',
	);

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'affiliate_plan' => array(),
		'advertisement' => array(),
		'advertisement_group' => array(),
		'benchmark' => array(),
		'cloud' => array(),
		'company_department_action_access' => array(),
		'company_department_module' => array(),
		'company_site' => array(),
		'company' => array('through' => 'company_site'),
		'counter' => array(),
		'counter_browser' => array(),
		'counter_device' => array(),
		'counter_display' => array(),
		'counter_os' => array(),
		'counter_page' => array(),
		'counter_referrer' => array(),
		'counter_searchquery' => array(),
		'counter_session' => array(),
		'counter_useragent' => array(),
		'counter_visit' => array(),
		'cdn_site' => array(),
		'deal' => array(),
		'deal_template' => array(),
		'document' => array(),
		'document_dir' => array(),
		'document_status' => array(),
		'dms_document' => array(),
		'dms_class' => array(),
		'dms_case_archive' => array(),
		'dms_case_destruction' => array(),
		'dms_communication' => array(),
		'dms_field' => array(),
		'dms_field_dir' => array(),
		'dms_state' => array(),
		'dms_template_instruction_dir' => array(),
		'dms_template_instruction' => array(),
		'dms_participant' => array(),
		'dms_workflow_template_dir' => array(),
		'dms_workflow_template' => array(),
		'dms_workflow' => array(),
		'dms_workflow' => array(),
		'dms_document_transfer' => array(),
		'dms_document_checkpoint' => array(),
		'field' => array(),
		'form' => array(),
		'forum' => array(),
		'helpdesk' => array(),
		'informationsystem' => array(),
		'informationsystem_dir'	=> array(),
		'lead' => array(),
		'lead_need' => array(),
		'lead_maturity' => array(),
		'lead_status' => array(),
		'list' => array(),
		'list_dir' => array(),
		'maillist' => array(),
		'poll_group' => array(),
		'search_log' => array(),
		'search_page' => array(),
		'seo_site' => array(),
		'shop' => array(),
		'shop_dir' => array(),
		'shortlink' => array(),
		'shortlink_dir' => array(),
		'siteuser' => array(),
		'siteuser_property' => array(),
		'siteuser_property_dir' => array(),
		'siteuser_group' => array(),
		'siteuser_identity_provider' => array(),
		'siteuser_relationship_type' => array(),
		'site_alias' => array(),
		'structure' => array(),
		'structure_property' => array(),
		'structure_property_dir' => array(),
		'structure_menu' => array(),
		'template' => array(),
		'template_dir' => array(),
		'webhook' => array(),
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'user' => array()
	);

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'sites.sorting' => 'ASC',
		'sites.name' => 'ASC'
	);

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'name' => '',
		'active' => 1,
		'coding' => 'UTF-8',
		'sorting' => 0,
		'locale' => 'ru_RU.utf8',
		'max_size_load_image' => 70,
		'max_size_load_image_big' => 300,
		'send_attendance_report' => 1,
		'date_format' => '%d.%m.%Y',
		'date_time_format' => '%d.%m.%Y %H:%M:%S',
		'error' => 'E_ALL',
		'error404' => 0,
		'error403' => 0,
		'robots' => "User-Agent: *\nDisallow: /admin\nDisallow: /search\nDisallow: /templates\nDisallow: /showbanner\nDisallow: /captcha.php\nDisallow: /403\nDisallow: /404\nAllow: /hostcmsfiles/css/*\nAllow: /hostcmsfiles/js/*\nAllow: /hostcmsfiles/jquery/*",
		'closed' => 0,
		'protect' => 1,
		'safe_email' => 1,
		'html_cache_use' => 0,
		'html_cache_with' => '/*',
		'html_cache_clear_probability' => 10000,
		'uploaddir' => 'upload/',
		'nesting_level' => 3,
		'timezone' => ''
	);

	/**
	 * Constructor.
	 * @param int $id entity ID
	 */
	public function __construct($id = NULL)
	{
		parent::__construct($id);

		if (is_null($id) && !$this->loaded())
		{
			$oUser = Core_Auth::getCurrentUser();
			$this->_preloadValues['user_id'] = is_null($oUser) ? 0 : $oUser->id;
		}
	}

	/**
	 * Change status of activity for site
	 * @return self
	 */
	public function changeStatus()
	{
		$this->active = 1 - $this->active;
		return $this->save();
	}

	/**
	 * Get alias by name
	 * @param string $aliasName name
	 * @return Site_Alias_Model|NULL
	 */
	public function getByAlias($aliasName)
	{
		$oSiteAliases = Core_Entity::factory('Site_Alias')->findAlias($aliasName);

		return !is_null($oSiteAliases)
			? $oSiteAliases->Site
			: NULL;
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event site.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		if (Core::moduleIsActive('advertisement'))
		{
			$this->Advertisements->deleteAll(FALSE);
			$this->Advertisement_Groups->deleteAll(FALSE);
		}

		if (Core::moduleIsActive('benchmark'))
		{
			$this->Benchmarks->deleteAll(FALSE);
		}

		if (Core::moduleIsActive('cdn'))
		{
			$this->Cdn_Sites->deleteAll(FALSE);
		}

		if (Core::moduleIsActive('cloud'))
		{
			$this->Clouds->deleteAll(FALSE);
		}

		if (Core::moduleIsActive('counter'))
		{
			$this->Counters->deleteAll(FALSE);
			$this->Counter_Browsers->deleteAll(FALSE);
			$this->Counter_Devices->deleteAll(FALSE);
			$this->Counter_Displays->deleteAll(FALSE);
			$this->Counter_Oses->deleteAll(FALSE);
			$this->Counter_Pages->deleteAll(FALSE);
			$this->Counter_Referrers->deleteAll(FALSE);
			$this->Counter_Searchqueries->deleteAll(FALSE);
			$this->Counter_Sessions->deleteAll(FALSE);
			$this->Counter_Useragents->deleteAll(FALSE);
			$this->Counter_Visits->deleteAll(FALSE);
		}

		$this->Documents->deleteAll(FALSE);
		$this->Document_Dirs->deleteAll(FALSE);
		$this->Document_Statuses->deleteAll(FALSE);

		if (Core::moduleIsActive('informationsystem'))
		{
			$this->Informationsystems->deleteAll(FALSE);
			$this->Informationsystem_Dirs->deleteAll(FALSE);
		}

		if (Core::moduleIsActive('list'))
		{
			$this->Lists->deleteAll(FALSE);
			$this->List_Dirs->deleteAll(FALSE);
		}

		if (Core::moduleIsActive('maillist'))
		{
			$this->Maillists->deleteAll(FALSE);
		}

		if (Core::moduleIsActive('poll'))
		{
			$this->Poll_Groups->deleteAll(FALSE);
		}

		if (Core::moduleIsActive('helpdesk'))
		{
			$this->Helpdesks->deleteAll(FALSE);
		}

		if (Core::moduleIsActive('forum'))
		{
			$this->Forums->deleteAll(FALSE);
		}

		if (Core::moduleIsActive('search'))
		{
			$this->Search_Logs->deleteAll(FALSE);
			$this->Search_Pages->deleteAll(FALSE);
		}

		if (Core::moduleIsActive('seo'))
		{
			$this->Seo_Sites->deleteAll(FALSE);
		}

		if (Core::moduleIsActive('shop'))
		{
			$this->Shops->deleteAll(FALSE);
			$this->Shop_Dirs->deleteAll(FALSE);
		}

		if (Core::moduleIsActive('siteuser'))
		{
			$this->Affiliate_Plans->deleteAll(FALSE);
			$this->Siteusers->deleteAll(FALSE);
			$this->Siteuser_Groups->deleteAll(FALSE);
			$this->Siteuser_Identity_Providers->deleteAll(FALSE);

			// Доп. св-ва пользователей сайта
			$oSiteuser_Property_List = Core_Entity::factory('Siteuser_Property_List', $this->id);
			$oSiteuser_Property_List->Properties->deleteAll(FALSE);
			$oSiteuser_Property_List->Property_Dirs->deleteAll(FALSE);
		}

		if (Core::moduleIsActive('form'))
		{
			$this->Forms->deleteAll(FALSE);
		}

		if (Core::moduleIsActive('company'))
		{
			$this->Company_Sites->deleteAll(FALSE);
			$this->Company_Department_Action_Accesses->deleteAll(FALSE);
			$this->Company_Department_Modules->deleteAll(FALSE);
		}

		if (Core::moduleIsActive('shortlink'))
		{
			$this->Shortlinks->deleteAll(FALSE);
			$this->Shortlink_Dirs->deleteAll(FALSE);
		}

		if (Core::moduleIsActive('deal'))
		{
			$this->Deals->deleteAll(FALSE);
			$this->Deal_Templates->deleteAll(FALSE);
		}

		if (Core::moduleIsActive('lead'))
		{
			$this->Leads->deleteAll(FALSE);
			$this->Lead_Needs->deleteAll(FALSE);
			$this->Lead_Maturities->deleteAll(FALSE);
			$this->Lead_Statuses->deleteAll(FALSE);
		}

		if (Core::moduleIsActive('field'))
		{
			$this->Fields->deleteAll(FALSE);
		}

		if (Core::moduleIsActive('webhook'))
		{
			$this->Webhooks->deleteAll(FALSE);
		}

		if (Core::moduleIsActive('dms'))
		{
			$this->Dms_Documents->deleteAll(FALSE);
			$this->Dms_Classes->deleteAll(FALSE);
			$this->Dms_Communications->deleteAll(FALSE);
			$this->Dms_Fields->deleteAll(FALSE);
			$this->Dms_Field_Dirs->deleteAll(FALSE);
			$this->Dms_States->deleteAll(FALSE);
			$this->Dms_Participants->deleteAll(FALSE);
			$this->Dms_Template_Instruction_Dirs->deleteAll(FALSE);
			$this->Dms_Template_Instructions->deleteAll(FALSE);
			$this->Dms_Workflow_Template_Dirs->deleteAll(FALSE);
			$this->Dms_Workflow_Templates->deleteAll(FALSE);
			$this->Dms_Workflows->deleteAll(FALSE);
			$this->Dms_Document_Transfers->deleteAll(FALSE);
			$this->Dms_Document_Checkpoints->deleteAll(FALSE);
			$this->Dms_Case_Archives->deleteAll(FALSE);
			$this->Dms_Case_Destructions->deleteAll(FALSE);
		}

		$this->Site_Aliases->deleteAll(FALSE);

		$this->Structures->deleteAll(FALSE);
		$this->Structure_Menus->deleteAll(FALSE);

		$this->Templates->deleteAll(FALSE);
		$this->Template_Dirs->deleteAll(FALSE);

		// Удаление доп. св-в структуры сайта
		$oStructure_Property_List = Core_Entity::factory('Structure_Property_List', $this->id);
		$oStructure_Property_List->Properties->deleteAll(FALSE);

		$oStructure_Property_List->Property_Dirs->deleteAll(FALSE);

		// Удаление директории с доп. св-вами структуры
		try
		{
			Core_File::deleteDir(CMS_FOLDER . $this->uploaddir . 'structure_' . intval($this->id));
		} catch (Exception $e) {}

		$this->deleteFavicon();

		return parent::delete($primaryKey);
	}

	/**
	 * Delete favicon file
	 * @return self
	 */
	public function deleteFavicon()
	{
		if ($this->favicon != '')
		{
			try
			{
				Core_File::delete($this->getFaviconPath());

				$this->favicon = '';
				$this->save();
			} catch (Exception $e) {}
		}

		return $this;
	}

	/**
	 * Current alias
	 * @var Site_Alias_Model
	 */
	protected $_currentAlias = NULL;

	/**
	 * Get current alias
	 * @param boolean $bCache cache mode
	 * @return Site_Alias_Model|NULL
	 */
	public function getCurrentAlias($bCache = TRUE)
	{
		if ($bCache && !is_null($this->_currentAlias))
		{
			return $this->_currentAlias;
		}

		$siteAlias = $this->Site_Aliases;

		$siteAlias->queryBuilder()
			// ->clear()
			->where('current', '=', 1)
			->limit(1);

		$aSiteAlias = $siteAlias->findAll();
		if (count($aSiteAlias) > 0)
		{
			$aSiteAlias[0]->name = $aSiteAlias[0]->alias_name_without_mask;

			$bCache && $this->_currentAlias = $aSiteAlias[0];

			return $aSiteAlias[0];
		}

		return NULL;
	}

	/**
	 * Get first site
	 * @return Site_Model|NULL
	 */
	public function getFirstSite()
	{
		$this->queryBuilder()
			->clear()
			->orderBy('sorting', 'ASC')
			->limit(1);

		$result = $this->findAll();

		if (!empty($result))
		{
			return $result[0];
		}

		return NULL;
	}

	/**
	 * Get favicon path
	 */
	protected function _getFaviconPath()
	{
		return CMS_FOLDER . $this->uploaddir . "favicon";
	}

	/**
	 * Get favicon file path
	 * @return string
	 */
	public function getFaviconPath()
	{
		return $this->_getFaviconPath() . '/' . $this->favicon;
	}

	/**
	 * Get favicon file href
	 * @return string
	 */
	public function getFaviconHref()
	{
		return '/' . $this->uploaddir . "favicon/" . $this->favicon;
	}

	/**
	 * Specify favicon file
	 * @param string $fileSourcePath source file path
	 * @return self
	 */
	public function saveFavicon($name, $fileSourcePath)
	{
		$this->deleteFavicon();

		$this->favicon = $name;
		$this->save();

		$faviconPath = $this->_getFaviconPath();

		if (!is_dir($faviconPath))
		{
			try
			{
				Core_File::mkdir($faviconPath);
			} catch (Exception $e) {}
		}

		Core_File::upload($fileSourcePath, $this->getFaviconPath());

		return $this;
	}

	public function imgBackend()
	{
		return $this->favicon != ''
			? '<img width="16" src="' . $this->getFaviconHref() . '"/>'
			: '';
	}

	/**
	 * Save object.
	 *
	 * @return Core_Entity
	 */
	public function save()
	{
		// Создание директории uploaddir
		if (strlen($this->uploaddir))
		{
			$uploaddir_path = CMS_FOLDER . $this->uploaddir;

			if (!is_dir($uploaddir_path))
			{
				try
				{
					Core_File::mkdir($uploaddir_path, CHMOD, TRUE);
				} catch (Exception $e) {}
			}
		}

		return parent::save();
	}

	/**
	 * Copy object
	 * @return Core_Entity
	 * @hostcms-event site.onAfterRedeclaredCopy
	 */
	public function copy()
	{
		$newObject = parent::copy();

		try {
			Core_File::copy($this->getFaviconPath(), $newObject->getFaviconPath());
		}
		catch (Exception $e) {}

		$aReplace = array();

		// Advertisement
		if (Core::moduleIsActive('advertisement'))
		{
			// Список баннеров сайта
			$aAdvertisements = $this->Advertisements->findAll(FALSE);

			// Массив, сожержащий копии баннеров
			$aMatchAdvertisements = array();

			// Цикл по баннерам
			foreach ($aAdvertisements as $oAdvertisement)
			{
				// Копируем баннер
				$oNewAdvertisement = $oAdvertisement->copy();
				$newObject->add($oNewAdvertisement);

				$aMatchAdvertisements[$oAdvertisement->id] = $oNewAdvertisement;
			}

			// Список групп баннеров сайта
			$aAdvertisement_Groups = $this->Advertisement_Groups->findAll(FALSE);

			// Цикл по группам баннеров
			foreach ($aAdvertisement_Groups as $oAdvertisement_Group)
			{
				// Копируем группу баннеров
				$oNewAdvertisement_Group = $oAdvertisement_Group->copy();

				// Связываем скопированную группу с сайтом
				$newObject->add($oNewAdvertisement_Group);

				// Получаем связи группы баннеров с баннерами
				$aAdvertisement_Group_Lists = $oNewAdvertisement_Group->Advertisement_Group_Lists->findAll(FALSE);

				// Цикл по связям группы баннеров с баннерами
				foreach ($aAdvertisement_Group_Lists as $oAdvertisement_Group_List)
				{
					if (isset($aMatchAdvertisements[$oAdvertisement_Group_List->advertisement_id]))
					{
						$oNewAdvertisement = $aMatchAdvertisements[$oAdvertisement_Group_List->advertisement_id];
						$oNewAdvertisement->add($oAdvertisement_Group_List);
					}
				}

				$aReplace["'Advertisement_Group', {$oAdvertisement_Group->id})"] = "'Advertisement_Group', " . $oNewAdvertisement_Group->id . ")";
			}

			unset($aMatchAdvertisements);
		}

		// Documents
		// Список статусов документов
		$aDocument_Statuses = $this->Document_Statuses->findAll(FALSE);

		// Массив для хранения копий статусов документов
		$aMatchDocument_Statuses = array();

		// Цикл по статусам документов
		foreach ($aDocument_Statuses as $oDocument_Status)
		{
			// Копируем статус документа
			$oNewDocument_Status = $oDocument_Status->copy();

			$newObject->add($oNewDocument_Status);
			$aMatchDocument_Statuses[$oDocument_Status->id] = $oNewDocument_Status;
		}

		// Список разделов документов
		$aDocument_Dirs = $this->Document_Dirs->findAll(FALSE);

		$aMatchDocument_Dirs = array();

		// Цикл по разделам документов
		foreach ($aDocument_Dirs as $oDocument_Dir)
		{
			// Копируем разделы документов
			$oNewDocument_Dir = clone $oDocument_Dir;
			$newObject->add($oNewDocument_Dir);

			$aMatchDocument_Dirs[$oDocument_Dir->id] = $oNewDocument_Dir;
		}

		// Получаем скопированные разделы документов
		$aNewDocument_Dirs = $newObject->Document_Dirs->findAll(FALSE);

		// В цикле заменяем идентификаторы родительских разделов
		foreach ($aNewDocument_Dirs as $oNewDocument_Dir)
		{
			if (isset($aMatchDocument_Dirs[$oNewDocument_Dir->parent_id]))
			{
				$oNewDocument_Dir->parent_id = $aMatchDocument_Dirs[$oNewDocument_Dir->parent_id]->id;

				$oNewDocument_Dir->save();
			}
		}

		// Получаем документы, принадлежащие сайту
		$aDocuments = $this->Documents->findAll(FALSE);

		$aMatch_Documents = array();

		foreach ($aDocuments as $oDocument)
		{
			//$oNewDocument = clone $oDocument;
			$oNewDocument = $oDocument->copy();

			if (isset($aMatchDocument_Dirs[$oNewDocument->document_dir_id]))
			{
				$oNewDocument->document_dir_id = $aMatchDocument_Dirs[$oNewDocument->document_dir_id]->id;
			}

			if (isset($aMatchDocument_Statuses[$oNewDocument->document_status_id]))
			{
				$oNewDocument_Status = $aMatchDocument_Statuses[$oNewDocument->document_status_id];

				// Меняем статус скопированного документа на скопированный статус документа
				$oNewDocument->document_status_id = $oNewDocument_Status->id;
			}

			$newObject->add($oNewDocument);

			$aMatch_Documents[$oDocument->id] = $oNewDocument;

			$aReplace["'Document', {$oDocument->id})"] = "'Document', " . $oNewDocument->id . ")";
		}

		unset($aMatchDocument_Statuses);
		unset($aMatchDocument_Dirs);

		// Menu
		$aMatchStructure_Menus = array();

		$aStructure_Menus = $this->Structure_Menus->findAll(FALSE);
		foreach ($aStructure_Menus as $oStructure_Menu)
		{
			$oNewStructure_Menu = $oStructure_Menu->copy();
			$newObject->add($oNewStructure_Menu);

			$aMatchStructure_Menus[$oStructure_Menu->id] = $oNewStructure_Menu;

			$aReplace["->menu({$oStructure_Menu->id})"] = "->menu({$oNewStructure_Menu->id})";
		}

		// Structures
		// Получаем доп. свойства узлов структуры
		$aStructure_Properties = $this->Structure_Properties->findAll(FALSE);

		$aMatchStructure_Properties = array();
		foreach ($aStructure_Properties as $oStructure_Property)
		{
			$oProperty = $oStructure_Property->Property;

			if ($oProperty->id)
			{
				$oNewProperty = $oProperty->copy();
				$oNewStructure_Property = $oNewProperty->Structure_Property;
				$oNewStructure_Property->site_id = $newObject->id;
				$oNewStructure_Property->save();

				$aMatchStructure_Properties[$oProperty->id] = $oNewProperty;
			}
		}

		// Получаем разделы доп. свойств узлов структуры
		$aStructure_Property_Dirs = $this->Structure_Property_Dirs->findAll(FALSE);

		$aMatchPropertyDirs = array();
		foreach ($aStructure_Property_Dirs as $oStructure_Property_Dir)
		{
			$oNewStructure_Property_Dir = clone $oStructure_Property_Dir;
			$oProperty_Dir = $oStructure_Property_Dir->Property_Dir;

			if ($oProperty_Dir->id)
			{
				$oNewProperty_Dir = clone $oProperty_Dir;
				$oNewProperty_Dir->save();
				$oNewStructure_Property_Dir->property_dir_id = $oNewProperty_Dir->id;
				$newObject->add($oNewStructure_Property_Dir);

				$aMatchPropertyDirs[$oProperty_Dir->id] = $oNewProperty_Dir;
			}
		}

		// Обновление parent_id для иерархии директорий
		$aNewStructure_Property_Dirs = $newObject->Structure_Property_Dirs->findAll(FALSE);

		foreach ($aNewStructure_Property_Dirs as $oNewStructure_Property_Dir)
		{
			$oNewProperty_Dir = $oNewStructure_Property_Dir->Property_Dir;

			if (isset($aMatchPropertyDirs[$oNewProperty_Dir->parent_id]))
			{
				$oNewProperty_Dir->parent_id = $aMatchPropertyDirs[
					$oNewProperty_Dir->parent_id
				]->id;

				$oNewProperty_Dir->save();
			}
		}

		// Обновляем значение родительской директории свойства
		$aNewStructure_Properties = $newObject->Structure_Properties->findAll(FALSE);
		foreach ($aNewStructure_Properties as $oNewStructure_Property)
		{
			$oNewProperty = $oNewStructure_Property->Property;

			if (isset($aMatchPropertyDirs[$oNewProperty->property_dir_id]))
			{
				$oNewProperty->property_dir_id = $aMatchPropertyDirs[$oNewProperty->property_dir_id]->id;
				$oNewProperty->save();
			}
		}

		$aStructures = $this->Structures->findAll(FALSE);
		$aMatchStructures = array();
		foreach ($aStructures as $oStructure)
		{
			$oNewStructure = $oStructure->copy();
			$oNewStructure->path = $oStructure->path;

			if (isset($aMatchStructure_Menus[$oNewStructure->structure_menu_id]))
			{
				$oNewStructure->structure_menu_id = $aMatchStructure_Menus[$oNewStructure->structure_menu_id]->id;
			}

			//$newObject->add($oNewStructure);
			$aMatchStructures[$oStructure->id] = $oNewStructure;

			// Замена значений свойств
			$aProperty_Values = $oNewStructure->getPropertyValues(FALSE);

			$oldDir = $oNewStructure->getDirPath();

			$newObject->add($oNewStructure);

			// Change dir
			$newDir = $oNewStructure->getDirPath();

			if (is_dir($oldDir))
			{
				try
				{
					if (!is_dir($newDir))
					{
						// Create all parent dirs
						Core_File::mkdir($newDir, CHMOD, TRUE);
						// Delete just $newDir
						Core_File::deleteDir($newDir);
						clearstatcache();
					}
					Core_File::rename($oldDir, $newDir);
				} catch (Exception $e) {}
			}

			foreach ($aProperty_Values as $oProperty_Value)
			{
				if (isset($aMatchStructure_Properties[$oProperty_Value->property_id]))
				{
					$oProperty_Value->property_id = $aMatchStructure_Properties[
						$oProperty_Value->property_id
					]->id;
				}

				$oProperty_Value->save();
			}
		}
		unset($aMatchStructure_Properties);
		unset($aMatchPropertyDirs);

		// Lists
		if (Core::moduleIsActive('list'))
		{
			// Получаем список разделов списков
			$aList_Dirs = $this->List_Dirs->findAll(FALSE);

			$aMatchList_Dirs = array();
			foreach ($aList_Dirs as $oList_Dir)
			{
				$oNewList_Dir = clone $oList_Dir;

				$newObject->add($oNewList_Dir);
				$aMatchList_Dirs[$oList_Dir->id] = $oNewList_Dir;
			}

			$aNewList_Dirs = $newObject->List_Dirs->findAll(FALSE);
			foreach ($aNewList_Dirs as $oNewList_Dir)
			{
				if (isset($aMatchList_Dirs[$oNewList_Dir->parent_id]))
				{
					$oNewList_Dir->parent_id = $aMatchList_Dirs[$oNewList_Dir->parent_id]->id;
					$oNewList_Dir->save();
				}
			}

			$aLists = $this->Lists->findAll(FALSE);

			$aMatchLists = array();
			foreach ($aLists as $oList)
			{
				$oNewList = $oList->copy();
				if (isset($aMatchList_Dirs[$oNewList->list_dir_id]))
				{
					$oNewList->list_dir_id = $aMatchList_Dirs[$oNewList->list_dir_id]->id;
				}

				$newObject->add($oNewList);

				$aMatchLists[$oList->id] = $oNewList;
			}

			unset($aMatchList_Dirs);

			//$this->Lists->deleteAll(FALSE);
			//$this->List_Dirs->deleteAll(FALSE);
		}

		// Polls
		if (Core::moduleIsActive('poll'))
		{
			//$this->Poll_Groups->deleteAll(FALSE);

			// Получаем список групп опросов
			$aPoll_Groups = $this->Poll_Groups->findAll(FALSE);

			foreach ($aPoll_Groups as $oPoll_Group)
			{
				$oNewPoll_Group = $oPoll_Group->copy();
				$newObject->add($oNewPoll_Group);

				$aReplace["'Poll_Group', {$oPoll_Group->id})"] = "'Poll_Group', " . $oNewPoll_Group->id . ")";
			}
		}

		$aMatchSiteuser_Groups = array();

		if (Core::moduleIsActive('siteuser'))
		{
			// Получаем список групп пользователей
			$aSiteuser_Groups = $this->Siteuser_Groups->findAll(FALSE);

			foreach ($aSiteuser_Groups as $oSiteuser_Group)
			{
				$oNewSiteuser_Group = $oSiteuser_Group->copy();
				$newObject->add($oNewSiteuser_Group);

				$aMatchSiteuser_Groups[$oSiteuser_Group->id] = $oNewSiteuser_Group;
			}

			// Получаем доп. свойства пользователей сайта
			$aSiteuser_Properties = $this->Siteuser_Properties->findAll(FALSE);

			$aMatchProperties = array();
			foreach ($aSiteuser_Properties as $oSiteuser_Property)
			{
				$oProperty = $oSiteuser_Property->Property;

				if ($oProperty->id)
				{
					$oNewProperty = $oProperty->copy();
					$oNewSiteuser_Property = $oNewProperty->Siteuser_Property;
					$oNewSiteuser_Property->site_id = $newObject->id;
					$oNewSiteuser_Property->save();

					$aMatchProperties[$oProperty->id] = $oNewProperty;
				}
			}

			// Получаем разделы доп. свойств пользователей сайта
			$aSiteuser_Property_Dirs = $this->Siteuser_Property_Dirs->findAll(FALSE);

			$aMatchPropertyDirs = array();
			foreach ($aSiteuser_Property_Dirs as $oSiteuser_Property_Dir)
			{
				$oNewSiteuser_Property_Dir = clone $oSiteuser_Property_Dir;

				$oProperty_Dir = $oSiteuser_Property_Dir->Property_Dir;

				if ($oProperty_Dir->id)
				{
					$oNewProperty_Dir = clone $oProperty_Dir;
					$oNewProperty_Dir->save();
					$oNewSiteuser_Property_Dir->property_dir_id = $oNewProperty_Dir->id;
					$newObject->add($oNewSiteuser_Property_Dir);

					$aMatchPropertyDirs[$oProperty_Dir->id] = $oNewProperty_Dir;
				}
			}

			// Обновление parent_id для иерархии директорий
			$aNewSiteuser_Property_Dirs = $newObject->Siteuser_Property_Dirs->findAll(FALSE);

			foreach ($aNewSiteuser_Property_Dirs as $oNewSiteuser_Property_Dir)
			{
				$oNewProperty_Dir = $oNewSiteuser_Property_Dir->Property_Dir;

				if (isset($aMatchPropertyDirs[$oNewProperty_Dir->parent_id]))
				{
					$oNewProperty_Dir->parent_id = $aMatchPropertyDirs[
						$oNewProperty_Dir->parent_id
					]->id;

					$oNewProperty_Dir->save();
				}
			}

			// Обновляем значение родительской директории свойств пользователей сайта
			$aNewSiteuser_Properties = $newObject->Siteuser_Properties->findAll(FALSE);
			foreach ($aNewSiteuser_Properties as $oNewSiteuser_Property)
			{
				$oNewProperty = $oNewSiteuser_Property->Property;

				if (isset($aMatchPropertyDirs[$oNewProperty->property_dir_id]))
				{
					$oNewProperty->property_dir_id = $aMatchPropertyDirs[$oNewProperty->property_dir_id]->id;
					$oNewProperty->save();
				}
			}

			$aMatchIdentityProviders = array();
			$aSiteuser_Identity_Providers = $this->Siteuser_Identity_Providers->findAll(FALSE);
			foreach ($aSiteuser_Identity_Providers as $oSiteuser_Identity_Provider)
			{
				$oNewProvider = $oSiteuser_Identity_Provider->copy();
				$aMatchIdentityProviders[$oSiteuser_Identity_Provider->id] = $oNewProvider;

				$newObject->add($oNewProvider);
			}

			$aMatchSiteusers = array();

			// Получаем список пользователей сайта
			$aSiteusers = $this->Siteusers->findAll(FALSE);
			foreach ($aSiteusers as $oSiteuser)
			{
				$oNewSiteuser = $oSiteuser->copy();
				$aMatchSiteusers[$oSiteuser->id] = $oNewSiteuser;

				// Копирование значений свойств пользователя
				$aProperty_Values = $oNewSiteuser->getPropertyValues(FALSE);

				$newObject->add($oNewSiteuser);

				$aSiteuser_Identities = $oSiteuser->Siteuser_Identities->findAll(FALSE);
				foreach ($aSiteuser_Identities as $oSiteuser_Identity)
				{
					if (isset($aMatchIdentityProviders[$oSiteuser_Identity->siteuser_identity_provider_id]))
					{
						$oNewSiteuser_Identity = $oSiteuser_Identity->copy();
						$oNewSiteuser_Identity->siteuser_id = $oNewSiteuser->id;
						$oNewSiteuser_Identity->siteuser_identity_provider_id = $aMatchIdentityProviders[$oSiteuser_Identity->siteuser_identity_provider_id];
						$oNewSiteuser_Identity->save();
					}
				}

				foreach ($aProperty_Values as $oProperty_Value)
				{
					//$oNewProperty_Value = $oProperty_Value->copy();

					if (isset($aMatchProperties[$oProperty_Value->property_id]))
					{
						$oProperty_Value->property_id = $aMatchProperties[
							$oProperty_Value->property_id
						]->id;
					}
					$oProperty_Value->save();
				}
			}

			// Цикл по пользователям сайта
			foreach ($aSiteusers as $oSiteuser)
			{
				$aSiteuser_Group_Lists = $oSiteuser->Siteuser_Group_Lists->findAll(FALSE);

				foreach ($aSiteuser_Group_Lists as $oSiteuser_Group_List)
				{
					//$oNewSiteuser_Group_List = $oSiteuser_Group_List->copy();
					$oNewSiteuser_Group_List = Core_Entity::factory('Siteuser_Group_List');

					if (isset($aMatchSiteuser_Groups[$oSiteuser_Group_List->siteuser_group_id])
					&& isset($aMatchSiteusers[$oSiteuser_Group_List->siteuser_id]))
					{
						$oNewSiteuser_Group = $aMatchSiteuser_Groups[$oSiteuser_Group_List->siteuser_group_id];
						$oNewSiteuser = $aMatchSiteusers[$oSiteuser_Group_List->siteuser_id];

						$oNewSiteuser_Group_List->siteuser_group_id = $oNewSiteuser_Group->id;
						$oNewSiteuser_Group_List->siteuser_id = $oNewSiteuser->id;
						$oNewSiteuser_Group_List->save();
					}
				}
			}

			// В цикле по пользователям сайта, в котором копируем афиллиатов пользователей
			/*
			foreach ($aSiteusers as $oSiteuser)
			{
				$aSiteuser_Affiliates = $oSiteuser->Siteuser_Affiliates->findAll(FALSE);

				foreach ($aSiteuser_Affiliates as $oSiteuser_Affiliate)
				{
					$aNewSiteuser_Affiliate = $oSiteuser_Affiliate->copy();

					if (isset($aMatchSiteusers[$aNewSiteuser_Affiliate->referral_siteuser_id])
					&& isset($aMatchSiteusers[$aNewSiteuser_Affiliate->siteuser_id]))
					{
						$oNewReferralSiteuser = $aMatchSiteusers[$aNewSiteuser_Affiliate->referral_siteuser_id];

						$oNewSiteuser = $aMatchSiteusers[$aNewSiteuser_Affiliate->siteuser_id];

						$aNewSiteuser_Affiliate->referral_siteuser_id = $oNewReferralSiteuser->id;
						$aNewSiteuser_Affiliate->siteuser_id = $oNewSiteuser->id;
						$aNewSiteuser_Affiliate->save();
					}
				}
			}
			*/
		}

		$aMatchInformationsystems = array();
		if (Core::moduleIsActive('informationsystem'))
		{
			// Informationsystems
			$aInformationsystem_Dirs = $this->Informationsystem_Dirs->findAll(FALSE);
			$aMatchInformationsystem_Dirs = array();

			foreach ($aInformationsystem_Dirs as $oInformationsystem_Dir)
			{
				$oNewInformationsystem_Dir = clone $oInformationsystem_Dir;
				$newObject->add($oNewInformationsystem_Dir);

				$aMatchInformationsystem_Dirs[$oInformationsystem_Dir->id] = $oNewInformationsystem_Dir;
			}

			// Получаем скопированные разделы информационных систем
			$aNewInformationsystem_Dirs = $newObject->Informationsystem_Dirs->findAll(FALSE);
			foreach ($aNewInformationsystem_Dirs as $oNewInformationsystem_Dir)
			{
				if (isset($aMatchInformationsystem_Dirs[$oNewInformationsystem_Dir->parent_id]))
				{
					$oNewInformationsystem_Dir->parent_id = $aMatchInformationsystem_Dirs[$oNewInformationsystem_Dir->parent_id]->id;

					$oNewInformationsystem_Dir->save();
				}
			}

			//Получаем список информационных систем, принадлежащих сайту
			$aInformationsystems = $this->Informationsystems->findAll(FALSE);

			// Цикл по информационным системам, находящимся в корне разделов информационных систем
			foreach ($aInformationsystems as $oInformationsystem)
			{
				$oNewInformationsystem = $oInformationsystem->copy();
				if (isset($aMatchInformationsystem_Dirs[$oInformationsystem->informationsystem_dir_id]))
				{
					$oNewInformationsystem->informationsystem_dir_id = $aMatchInformationsystem_Dirs[$oInformationsystem->informationsystem_dir_id]->id;
				}

				if (isset($aMatchStructures[$oNewInformationsystem->structure_id]))
				{
					$oNewInformationsystem->structure_id = $aMatchStructures[$oNewInformationsystem->structure_id]->id;
				}

				if (isset($aMatchSiteuser_Groups[$oNewInformationsystem->siteuser_group_id]))
				{
					$oNewInformationsystem->siteuser_group_id = $aMatchSiteuser_Groups[$oNewInformationsystem->siteuser_group_id]->id;
				}

				$newObject->add($oNewInformationsystem);

				$aMatchInformationsystems[$oInformationsystem->id] = $oNewInformationsystem;

				$aReplace["'Informationsystem', {$oInformationsystem->id})"] = "'Informationsystem', " . $oNewInformationsystem->id . ")";
			}

			unset($aMatchInformationsystem_Dirs);
		}

		$aMatchShops = array();
		if (Core::moduleIsActive('shop'))
		{
			$aShop_Dirs = $this->Shop_Dirs->findAll(FALSE);

			$aMatchShop_Dirs = array();
			foreach ($aShop_Dirs as $oShop_Dir)
			{
				//$oNewShop_Dir = $oShop_Dir->copy();
				$oNewShop_Dir = clone $oShop_Dir;
				$aMatchShop_Dirs[$oShop_Dir->id] = $oNewShop_Dir;

				$newObject->add($oNewShop_Dir);
			}

			//Получаем список магазинов принадлежащих сайту
			$oShops = $this->Shops;
			//$oShops->queryBuilder()->where('shop_dir_id', '=', 0);
			$aShops = $oShops->findAll(FALSE);

			// Цикл по магазинам, находящимся в корне разделов магазинов
			foreach ($aShops as $oShop)
			{
				$oNewShop = $oShop->copy();

				if (isset($aMatchShop_Dirs[$oNewShop->shop_dir_id]))
				{
					$oNewShop->shop_dir_id = $aMatchShop_Dirs[$oNewShop->shop_dir_id]->id;
				}

				if (isset($aMatchStructures[$oNewShop->structure_id]))
				{
					$oNewShop->structure_id = $aMatchStructures[$oNewShop->structure_id]->id;
				}

				if (isset($aMatchSiteuser_Groups[$oNewShop->siteuser_group_id]))
				{
					$oNewShop->siteuser_group_id = $aMatchSiteuser_Groups[$oNewShop->siteuser_group_id]->id;
				}

				$newObject->add($oNewShop);

				$aMatchShops[$oShop->id] = $oNewShop;

				$aReplace["'Shop', {$oShop->id})"] = "'Shop', " . $oNewShop->id . ")";
			}

			unset($aMatchShop_Dirs);
		}

		if (Core::moduleIsActive('forum'))
		{
			$aForums = $this->Forums->findAll(FALSE);
			$aMatchForums = array();
			foreach ($aForums as $oForum)
			{
				$oNewForum = clone $oForum;
				$newObject->add($oNewForum);

				$aMatchForums[$oForum->id] = $oNewForum;

				$aForum_Groups = $oForum->Forum_Groups->findAll(FALSE);
				foreach ($aForum_Groups as $oForum_Group)
				{
					$oNewForum_Group = clone $oForum_Group;
					$oNewForum->add($oNewForum_Group);

					$aForum_Categories = $oForum_Group->Forum_Categories->findAll(FALSE);

					foreach ($aForum_Categories as $oForum_Category)
					{
						$oNewForum_Category = clone $oForum_Category;
						$oNewForum_Group->add($oNewForum_Category);

						$aForum_Category_Siteuser_Groups = $oForum_Category->Forum_Category_Siteuser_Groups->findAll(FALSE);

						foreach ($aForum_Category_Siteuser_Groups as $oForum_Category_Siteuser_Group)
						{
							$oNewForum_Category_Siteuser_Group = $oForum_Category_Siteuser_Group->copy();

							if (isset($aMatchSiteuser_Groups[$oNewForum_Category_Siteuser_Group->siteuser_group_id]))
							{
								$oNewForum_Category_Siteuser_Group->siteuser_group_id = $aMatchSiteuser_Groups[$oNewForum_Category_Siteuser_Group->siteuser_group_id]->id;
							}

							$oNewForum_Category->add($oNewForum_Category_Siteuser_Group);
						}
					}
				}
			}
		}

		/*if (Core::moduleIsActive('seo'))
		{
			$aSeo_Sites = $this->Seo_Sites->findAll(FALSE);
			foreach ($aSeo_Sites as $oSeo_Site)
			{
				$newObject->add($oSeo_Site->copy());
			}
		}*/

		if (Core::moduleIsActive('maillist'))
		{
			// Получаем список рассылок
			$aMaillists = $this->Maillists->findAll(FALSE);

			foreach ($aMaillists as $oMaillist)
			{
				// Копируем рассылку
				$oNewMaillist = clone $oMaillist;
				$newObject->add($oNewMaillist);

				// Получаем связи групп пользователей, которым доступна подписка
				$aMaillist_Siteuser_Groups = $oMaillist->Maillist_Siteuser_Groups->findAll(FALSE);

				foreach ($aMaillist_Siteuser_Groups as $oMaillist_Siteuser_Group)
				{
					// Копируем связь группы пользователей с подпиской
					$oNewMaillist_Siteuser_Group = $oMaillist_Siteuser_Group->copy();

					if (isset($aMatchSiteuser_Groups[$oNewMaillist_Siteuser_Group->siteuser_group_id]))
					{
						$oNewMaillist_Siteuser_Group->siteuser_group_id = $aMatchSiteuser_Groups[$oNewMaillist_Siteuser_Group->siteuser_group_id]->id;
						$oNewMaillist->add($oNewMaillist_Siteuser_Group);
					}
				}
			}
		}

		if (Core::moduleIsActive('helpdesk'))
		{
			$aHelpdesks = $this->Helpdesks->findAll(FALSE);

			$aMatchHelpdesks = array();

			foreach ($aHelpdesks as $oHelpdesk)
			{
				//$oNewHelpdesk = $oHelpdesk->copy();

				$oNewHelpdesk = clone $oHelpdesk;

				$aHelpdesk_Categories = $oHelpdesk->Helpdesk_Categories->findAll(FALSE);

				$aMatchHelpdesk_Categories = array();

				foreach ($aHelpdesk_Categories as $oHelpdesk_Category)
				{
					$oNewHelpdesk_Category = clone $oHelpdesk_Category;
					$oNewHelpdesk->add($oNewHelpdesk_Category);

					$aMatchHelpdesk_Categories[$oHelpdesk_Category->id] = $oNewHelpdesk_Category;
				}

				$aNewHelpdesk_Categories = $oNewHelpdesk->Helpdesk_Categories->findAll(FALSE);

				foreach ($aNewHelpdesk_Categories as $oNewHelpdesk_Category)
				{
					if (isset($aMatchHelpdesk_Categories[$oNewHelpdesk_Category->parent_id]))
					{
						$oNewHelpdesk_Category->parent_id = $aMatchHelpdesk_Categories[$oNewHelpdesk_Category->parent_id]->id;
						$oNewHelpdesk_Category->save();
					}
				}

				// Получаем список статусов
				$aMatchHelpdesk_Statuses = array();

				$aHelpdesk_Statuses = $oHelpdesk->Helpdesk_Statuses->findAll(FALSE);
				foreach ($aHelpdesk_Statuses as $oHelpdesk_Status)
				{
					$oNewHelpdesk_Status = $oHelpdesk_Status->copy();

					$oNewHelpdesk_Status->helpdesk_id = $oNewHelpdesk->id;
					$oNewHelpdesk_Status->save();

					//$newObject->add($oNewHelpdesk_Status);
					$aMatchHelpdesk_Statuses[$oHelpdesk_Status->id] = $oNewHelpdesk_Status;
				}

				if (isset($aMatchHelpdesk_Statuses[$oNewHelpdesk->helpdesk_status_reply_id]))
				{
					$oNewHelpdesk->helpdesk_status_reply_id = $aMatchHelpdesk_Statuses[$oNewHelpdesk->helpdesk_status_reply_id]->id;
				}

				if (isset($aMatchHelpdesk_Statuses[$oNewHelpdesk->helpdesk_status_new_id]))
				{
					$oNewHelpdesk->helpdesk_status_new_id = $aMatchHelpdesk_Statuses[$oNewHelpdesk->helpdesk_status_new_id]->id;
				}

				$oNewHelpdesk->save();

				unset($aMatchHelpdesk_Statuses);

				// Получаем список уровней критичности
				$aHelpdesk_Criticality_Levels = $oHelpdesk->Helpdesk_Criticality_Levels->findAll(FALSE);
				foreach ($aHelpdesk_Criticality_Levels as $oHelpdesk_Criticality_Level)
				{
					$oNewHelpdesk->add($oHelpdesk_Criticality_Level->copy());
				}

				// Получаем список праздничных дней
				$aHelpdesk_Holidays = $oHelpdesk->Helpdesk_Holidays->findAll(FALSE);
				foreach ($aHelpdesk_Holidays as $oHelpdesk_Holiday)
				{
					$oNewHelpdesk->add($oHelpdesk_Holiday->copy());
				}

				// Получаем список рабочих часов
				$aHelpdesk_Working_Hours = $oHelpdesk->Helpdesk_Working_Hours->findAll(FALSE);
				foreach ($aHelpdesk_Working_Hours as $oHelpdesk_Working_Hour)
				{
					$oNewHelpdesk->add($oHelpdesk_Working_Hour->copy());
				}

				if (isset($aMatchStructures[$oNewHelpdesk->structure_id]))
				{
					$oNewHelpdesk->structure_id = $aMatchStructures[$oNewHelpdesk->structure_id]->id;
				}
				$newObject->add($oNewHelpdesk);

				$aMatchHelpdesks[$oHelpdesk->id] = $oNewHelpdesk;
			}
		}

		$aSite_Aliases = $this->Site_Aliases->findAll(FALSE);
		foreach ($aSite_Aliases as $oSite_Alias)
		{
			$newObject->add($oSite_Alias->copy());
		}

		// Templates
		// Получаем список разделов макетов сайта
		$aTemplate_Dirs = $this->Template_Dirs->findAll(FALSE);

		$aMatchTemplate_Dirs = array();

		// В цикле копируем разделы макетов сайта
		foreach ($aTemplate_Dirs as $oTemplate_Dir)
		{
			$oNewTemplate_Dir = clone $oTemplate_Dir;
			$newObject->add($oNewTemplate_Dir);

			$aMatchTemplate_Dirs[$oTemplate_Dir->id] = $oNewTemplate_Dir;
		}

		$aNewTemplate_Dirs = $newObject->Template_Dirs->findAll(FALSE);

		// В цикле меняем идентификаторы родительских разделов на идентификаторы копий
		foreach ($aNewTemplate_Dirs as $oNewTemplate_Dir)
		{
			if (isset($aMatchTemplate_Dirs[$oNewTemplate_Dir->parent_id]))
			{
				$oNewTemplate_Dir->parent_id = $aMatchTemplate_Dirs[$oNewTemplate_Dir->parent_id]->id;
			}
		}

		$aTemplates = $this->Templates->findAll(FALSE);

		$aMatchTemplates = array();
		foreach ($aTemplates as $oTemplate)
		{
			$oNewTemplate = clone $oTemplate;

			$oNewTemplate->saveTemplateCssFile($oTemplate->loadTemplateCssFile());
			$oNewTemplate->saveTemplateFile(str_replace(array_keys($aReplace), array_values($aReplace), $oTemplate->loadTemplateFile()));

			if (isset($aMatchTemplate_Dirs[$oNewTemplate->template_dir_id]))
			{
				$oNewTemplate->template_dir_id = $aMatchTemplate_Dirs[$oNewTemplate->template_dir_id]->id;
			}

			$newObject->add($oNewTemplate);

			// Template_Sections
			$aTemplate_Sections = $oTemplate->Template_Sections->findAll(FALSE);

			foreach ($aTemplate_Sections as $oTemplate_Section)
			{
				$oNew_Template_Section = clone $oTemplate_Section;
				$oNewTemplate->add($oNew_Template_Section);

				// Template_Section_Libs
				$aTemplate_Section_Libs = $oTemplate_Section->Template_Section_Libs->findAll(FALSE);

				foreach ($aTemplate_Section_Libs as $oTemplate_Section_Lib)
				{
					$oNew_Template_Section_Lib = clone $oTemplate_Section_Lib;
					$oNew_Template_Section->add($oNew_Template_Section_Lib);
				}
			}

			$aMatchTemplates[$oTemplate->id] = $oNewTemplate;
		}

		$aNewTemplates = $newObject->Templates->findAll(FALSE);
		foreach ($aNewTemplates as $oNewTemplate)
		{
			if (isset($aMatchTemplates[$oNewTemplate->template_id]))
			{
				$oNewTemplate->template_id = $aMatchTemplates[$oNewTemplate->template_id]->id;
				$oNewTemplate->save();
			}
		}

		$aDocuments = $newObject->Documents->findAll(FALSE);
		foreach ($aDocuments as $oDocument)
		{
			if (isset($aMatchTemplates[$oDocument->template_id]))
			{
				$oDocument->template_id = $aMatchTemplates[$oDocument->template_id]->id;
				$oDocument->save();
			}
		}

		// Формы
		if (Core::moduleIsActive('form'))
		{
			$aForms = $this->Forms->findAll(FALSE);

			$aMatchForms = array();
			foreach ($aForms as $oForm)
			{
				$oNewForm =	$oForm->copy();
				$newObject->add($oNewForm);

				// Получаем поля типа "Список" скопированной формы
				$oForm_Fields = $oNewForm->Form_Fields;
				$oForm_Fields->queryBuilder()->where('type', '=', 6);

				$aForm_Fields = $oForm_Fields->findAll(FALSE);
				foreach ($aForm_Fields as $oForm_Field)
				{
					if (isset($aMatchLists[$oForm_Field->list_id]))
					{
						$oForm_Field->list_id = $aMatchLists[$oForm_Field->list_id]->id;
						$oForm_Field->save();
					}
				}

				$aMatchForms[$oForm->id] = $oNewForm;
			}
		}

		foreach ($aStructures as $oStructure)
		{
			if (isset($aMatchStructures[$oStructure->id]))
			{
				if ($oStructure->lib_id)
				{
					$array = $oStructure->Lib->getDat($oStructure->id);

					if (is_array($array))
					{
						if (isset($array['informationsystemId']) && isset($aMatchInformationsystems[$array['informationsystemId']]))
						{
							$array['informationsystemId'] = $aMatchInformationsystems[$array['informationsystemId']]->id;
						}

						if (isset($array['shopId']) && isset($aMatchShops[$array['shopId']]))
						{
							$array['shopId'] = $aMatchShops[$array['shopId']]->id;
						}

						if (isset($array['helpdeskId']) && isset($aMatchHelpdesks[$array['helpdeskId']]))
						{
							$array['helpdeskId'] = $aMatchHelpdesks[$array['helpdeskId']]->id;
						}

						if (isset($array['forum_id']) && isset($aMatchForums[$array['forum_id']]))
						{
							$array['forum_id'] = $aMatchForums[$array['forum_id']]->id;
						}

						if (isset($array['form_id']) && isset($aMatchForms[$array['form_id']]))
						{
							$array['form_id'] = $aMatchForms[$array['form_id']]->id;
						}

						$aMatchStructures[$oStructure->id]->Lib->saveDatFile($array, $aMatchStructures[$oStructure->id]->id);
					}
				}
			}
		}

		unset($aMatchInformationsystems);
		unset($aMatchShops);
		unset($aMatchHelpdesks);
		unset($aMatchForums);
		unset($aMatchForms);

		$aNewStructures = $newObject->Structures->findAll(FALSE);
		foreach ($aNewStructures as $oNewStructure)
		{
			if (isset($aMatchStructures[$oNewStructure->parent_id]))
			{
				$oNewStructure->parent_id = $aMatchStructures[$oNewStructure->parent_id]->id;
			}

			if (isset($aMatchTemplates[$oNewStructure->template_id]))
			{
				$oNewStructure->template_id = $aMatchTemplates[$oNewStructure->template_id]->id;
			}

			if (isset($aMatch_Documents[$oNewStructure->document_id]))
			{
				$oNewStructure->document_id = $aMatch_Documents[$oNewStructure->document_id]->id;
			}

			if (isset($aMatchSiteuser_Groups[$oNewStructure->siteuser_group_id]))
			{
				$oNewStructure->siteuser_group_id = $aMatchSiteuser_Groups[$oNewStructure->siteuser_group_id]->id;
			}

			$oNewStructure->save();
		}

		unset($aMatchStructures);
		unset($aMatchTemplate_Dirs);
		unset($aMatchTemplates);
		unset($aMatch_Documents);
		unset($aMatchSiteuser_Groups);

		Core_Event::notify($this->_modelName . '.onAfterRedeclaredCopy', $newObject, array($this));

		return $newObject;
	}

	/**
	 * Get first email
	 * @return string
	 */
	public function getFirstEmail()
	{
		$aEmails = array_map('trim', explode(',', $this->admin_email));
		return $aEmails[0];
	}

	/**
	 * Get first error email
	 * @return string
	 */
	public function getErrorEmail()
	{
		$aEmails = array_map('trim', explode(',', $this->error_email));
		return $aEmails[0];
	}

	/**
	 * Get site keys as array
	 * @return array
	 */
	public function getKeys()
	{
		$sKeys = trim(str_replace(array("\n", "\r", "\0", "\t", ), '', $this->key));
		return str_split($sKeys, 29);
	}

	/**
	 * Show alias data in XML
	 * @var boolean
	 */
	protected $_showXmlAlias = FALSE;

	/**
	 * Show alias in XML
	 * @param boolean $showXmlAlias
	 * @return self
	 */
	public function showXmlAlias($showXmlAlias = TRUE)
	{
		$this->_showXmlAlias = $showXmlAlias;
		return $this;
	}

	/**
	 * Show identity providers data in XML
	 * @var boolean
	 */
	protected $_showXmlSiteuserIdentityProviders = FALSE;

	/**
	 * Show identity providers in XML
	 * @param boolean $showXmlSiteuserIdentityProviders
	 * @return self
	 */
	public function showXmlSiteuserIdentityProviders($showXmlSiteuserIdentityProviders = TRUE)
	{
		$this->_showXmlSiteuserIdentityProviders = $showXmlSiteuserIdentityProviders;
		return $this;
	}

	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event site.onBeforeRedeclaredGetXml
	 */
	public function getXml()
	{
		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredGetXml', $this);

		$this->_prepareData();

		return parent::getXml();
	}

	/**
	 * Get stdObject for entity and children entities
	 * @return stdObject
	 * @hostcms-event site.onBeforeRedeclaredGetStdObject
	 */
	public function getStdObject($attributePrefix = '_')
	{
		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredGetStdObject', $this);

		$this->_prepareData();

		return parent::getStdObject($attributePrefix);
	}

	/**
	 * Prepare entity and children entities
	 * @return self
	 */
	protected function _prepareData()
	{
		if ($this->_showXmlAlias)
		{
			$oSite_Alias = $this->getCurrentAlias();
			!is_null($oSite_Alias) && $this->addEntity($oSite_Alias);
		}

		if ($this->_showXmlSiteuserIdentityProviders)
		{
			$oSiteuser_Identity_Providers = $this->Siteuser_Identity_Providers;
			$oSiteuser_Identity_Providers->queryBuilder()
				->where('siteuser_identity_providers.active', '=', 1);
			$aSiteuser_Identity_Providers = $oSiteuser_Identity_Providers->findAll();
			foreach ($aSiteuser_Identity_Providers as $oSiteuser_Identity_Provider)
			{
				$this->addEntity(
					$oSiteuser_Identity_Provider->clearEntities()
				);
			}
		}

		return $this;
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function nameBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		if ($this->https)
		{
			Core_Html_Entity::factory('Span')
				->class('badge badge-square badge-info')
				->style('font-size: 10px !important;')
				->value('HTTPS')
				->execute();
		}

		if ($this->protect)
		{
			Core_Html_Entity::factory('Span')
				->class('badge badge-square badge-azure')
				->style('font-size: 10px !important;')
				->value('<i class="fa fa-shield"></i>')
				->title(Core::_('Site.protect'))
				->execute();
		}

		if (strlen($this->csp))
		{
			Core_Html_Entity::factory('Span')
				->class('badge badge-square badge-maroon')
				->style('font-size: 10px !important;')
				->title('Content-Security-Policy: ' . $this->csp)
				->value('CSP')
				->execute();
		}

		$aSite_Aliases = $this->Site_Aliases->findAll();

		if (count($aSite_Aliases))
		{
			$oDiv = Core_Html_Entity::factory('Div')->class('margin-top-5');

			$aTmpSite_Aliases = array_slice($aSite_Aliases, 0, 12);
			foreach ($aTmpSite_Aliases as $oSite_Aliases)
			{
				$oDiv->add(
					$oSpan = Core_Html_Entity::factory('Span')
						->class('label label-' . ($oSite_Aliases->current ? 'palegreen' : 'gray'))
						->value(htmlspecialchars(substr($oSite_Aliases->name, 0, 4) === "xn--"
							? Core_Str::idnToUtf8($oSite_Aliases->name) . ' [' . $oSite_Aliases->name . ']'
							: Core_Str::cut($oSite_Aliases->name, 25)
						))
					);

				if ($oSite_Aliases->redirect)
				{
					$oSpan->add(
						Core_Html_Entity::factory('I')
							->class('fa fa-arrow-circle-right azure fa-small')
					);
				}
			}

			count($aTmpSite_Aliases) < count($aSite_Aliases) && $oDiv->add(
				Core_Html_Entity::factory('Span')
					->class('label label-gray')
					->value('…')
				);

			$oDiv->execute();
		}
	}
	
	public function timezoneBackend()
	{
		return $this->timezone == ''
			? '—'
			: $this->timezone;
	}

	/**
	 * Backup revision
	 * @return self
	 */
	public function backupRevision()
	{
		if (Core::moduleIsActive('revision'))
		{
			$aBackup = array(
				'name' => $this->name,
				'active' => $this->active,
				'coding' => $this->coding,
				'sorting' => $this->sorting,
				'locale' => $this->locale,
				'timezone' => $this->timezone,
				'max_size_load_image' => $this->max_size_load_image,
				'max_size_load_image_big' => $this->max_size_load_image_big,
				'admin_email' => $this->admin_email,
				'error_email' => $this->error_email,
				'lng' => $this->lng,
				'send_attendance_report' => $this->send_attendance_report,
				'date_format' => $this->date_format,
				'date_time_format' => $this->date_time_format,
				'error' => $this->error,
				'error404' => $this->error404,
				'error403' => $this->error403,
				'robots' => $this->robots,
				'key' => $this->key,
				'user_id' => $this->user_id,
				'closed' => $this->closed,
				'safe_email' => $this->safe_email,
				'protect' => $this->protect,
				'https' => $this->https,
				'html_cache_use' => $this->html_cache_use,
				'html_cache_with' => $this->html_cache_with,
				'html_cache_without' => $this->html_cache_without,
				'css_left' => $this->css_left,
				'css_right' => $this->css_right,
				'html_cache_clear_probability' => $this->html_cache_clear_probability,
				'notes' => $this->notes,
				'uploaddir' => $this->uploaddir,
				'nesting_level' => $this->nesting_level,
				'csp' => $this->csp,
			);

			Revision_Controller::backup($this, $aBackup);
		}

		return $this;
	}

	/**
	 * Rollback Revision
	 * @param int $revision_id Revision ID
	 * @return self
	 */
	public function rollbackRevision($revision_id)
	{
		if (Core::moduleIsActive('revision'))
		{
			$oRevision = Core_Entity::factory('Revision', $revision_id);

			$aBackup = json_decode($oRevision->value, TRUE);

			if (is_array($aBackup))
			{
				$this->name = Core_Array::get($aBackup, 'name');
				$this->active = Core_Array::get($aBackup, 'active');
				$this->coding = Core_Array::get($aBackup, 'coding');
				$this->sorting = Core_Array::get($aBackup, 'sorting');
				$this->locale = Core_Array::get($aBackup, 'locale');
				$this->timezone = Core_Array::get($aBackup, 'timezone');
				$this->max_size_load_image = Core_Array::get($aBackup, 'max_size_load_image');
				$this->max_size_load_image_big = Core_Array::get($aBackup, 'max_size_load_image_big');
				$this->admin_email = Core_Array::get($aBackup, 'admin_email');
				$this->error_email = Core_Array::get($aBackup, 'error_email');
				$this->lng = Core_Array::get($aBackup, 'lng');
				$this->send_attendance_report = Core_Array::get($aBackup, 'send_attendance_report');
				$this->date_format = Core_Array::get($aBackup, 'date_format');
				$this->date_time_format = Core_Array::get($aBackup, 'date_time_format');
				$this->error = Core_Array::get($aBackup, 'error');
				$this->error404 = Core_Array::get($aBackup, 'error404');
				$this->error403 = Core_Array::get($aBackup, 'error403');
				$this->robots = Core_Array::get($aBackup, 'robots');
				$this->key = Core_Array::get($aBackup, 'key');
				$this->user_id = Core_Array::get($aBackup, 'user_id');
				$this->closed = Core_Array::get($aBackup, 'closed');
				$this->safe_email = Core_Array::get($aBackup, 'safe_email');
				$this->protect = Core_Array::get($aBackup, 'protect');
				$this->https = Core_Array::get($aBackup, 'https');
				$this->html_cache_use = Core_Array::get($aBackup, 'html_cache_use');
				$this->html_cache_with = Core_Array::get($aBackup, 'html_cache_with');
				$this->html_cache_without = Core_Array::get($aBackup, 'html_cache_without');
				$this->css_left = Core_Array::get($aBackup, 'css_left');
				$this->css_right = Core_Array::get($aBackup, 'css_right');
				$this->html_cache_clear_probability = Core_Array::get($aBackup, 'html_cache_clear_probability');
				$this->notes = Core_Array::get($aBackup, 'notes');
				$this->uploaddir = Core_Array::get($aBackup, 'uploaddir');
				$this->nesting_level = Core_Array::get($aBackup, 'nesting_level');
				$this->csp = Core_Array::get($aBackup, 'csp');
				$this->save();
			}
		}

		return $this;
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event site.onBeforeGetRelatedSite
	 * @hostcms-event site.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}