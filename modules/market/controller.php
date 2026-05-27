<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Market.
 *
 * @package HostCMS
 * @subpackage Market
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Market_Controller extends Core_Servant_Properties
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'login',
		'contract',
		'pin',
		'cms_folder',
		'php_version',
		'mysql_version',
		'update_id',
		'domain',
		'update_server',
		'keys',
		'category_id',
		'items',
		'total',
		'page',
		'limit',
		'search',
		'installMode',
		'error',
		'controller',
		'options',
		'tmpDir',
		'order',
		'protocol',
		'backend',
		'path',
		'itemAction'
	);

	/**
	 * The singleton instances.
	 * @var mixed
	 */
	static public $instance = NULL;

	/**
	 * Categories
	 * @var array
	 */
	protected $_categories = array();

	/**
	 * Get categories
	 * @return array
	 */
	public function getCategories()
	{
		return $this->_categories;
	}

	/**
	 * Register an existing instance as a singleton.
	 * @return object
	 */
	static public function instance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->options = $this->items = array();
		$this->page = 1;
		$this->limit = 16;
		$this->error = 0;

		$this->path = Admin_Form_Controller::correctBackendPath('/{admin}/market/index.php');
		$this->itemAction = '';
	}

	/**
	 * Get directory path
	 * @return string
	 */
	public function getPath()
	{
		return CMS_FOLDER . 'hostcmsfiles/tmp/install';
	}

	/**
	 * Set market options
	 * @return self
	 */
	public function setMarketOptions()
	{
		$oHOSTCMS_UPDATE_NUMBER = Core_Entity::factory('Constant')->getByName('HOSTCMS_UPDATE_NUMBER');
		$update_id = !is_null($oHOSTCMS_UPDATE_NUMBER)
			? $oHOSTCMS_UPDATE_NUMBER->value
			: 0;

		$oSite = Core_Entity::factory('Site', CURRENT_SITE);

		$aSite_Alias_Names = array();

		$aSite_Aliases = $oSite->Site_Aliases->findAll();
		foreach ($aSite_Aliases as $oSite_Alias)
		{
			$aSite_Alias_Names[] = $oSite_Alias->name;
		}

		$oSite_Alias = $oSite->getCurrentAlias();
		$domain = !is_null($oSite_Alias)
			? $oSite_Alias->name
			: 'undefined';

		$this->login(defined('HOSTCMS_USER_LOGIN') ? HOSTCMS_USER_LOGIN : '')
			->contract(defined('HOSTCMS_CONTRACT_NUMBER') ? HOSTCMS_CONTRACT_NUMBER : '')
			->pin(defined('HOSTCMS_PIN_CODE') ? HOSTCMS_PIN_CODE : '')
			->cms_folder(CMS_FOLDER)
			->php_version(phpversion())
			->mysql_version(Core_DataBase::instance()->getVersion())
			->update_id($update_id)
			->domain($domain)
			->update_server(HOSTCMS_UPDATE_SERVER)
			->keys($aSite_Alias_Names)
			->protocol($oSite->https ? 'https' : 'http')
			->backend(Core::$mainConfig['backend']);

		return $this;
	}

	/**
	 * Shop groups
	 * @var array
	 */
	protected $_aShop_Groups = array();

	/**
	 * Parse group
	 * @param object $oXmlGroup
	 * @param integer $parentId
	 * @return array
	 */
	protected function _parseGroup($oXmlGroup, $parentId = 0)
	{
		foreach ($oXmlGroup as $value)
		{
			//if (intval($value->count))
			//{
				$oObject = new StdClass();
				$oObject->id = intval($value->attributes()->id);
				$oObject->name = strval($value->name);
				$oObject->description = strval($value->description);
				$oObject->count = intval($value->count);

				if ($oObject->id)
				{
					$this->_categories[$parentId][] = $oObject;
				}

				if (isset($value->shop_group) && count($value->shop_group))
				{
					$this->_parseGroup($value->shop_group, $oObject->id);
				}

				$this->_aShop_Groups[$oObject->id] = $oObject;
			//}
		}

		return $this->_categories;
	}

	/**
	 * Загрузка магазина
	 *
	 * @return Market_Controller
	 */
	public function getMarket()
	{
		// При установке беслпатной редакции данные будут пусты
		/*if ($this->contract !== '' && !is_null($this->contract)
			&& $this->pin !== '' && !is_null($this->pin)
		)
		{*/

		$md5_contract = md5($this->contract);
		$md5_pin = md5($this->pin);

		$url = 'https://' . $this->update_server . "/hostcmsupdate/market/?action=load_market&domain=" . rawurlencode((string) $this->domain) .
			'&protocol=' . rawurlencode((string) $this->protocol) .
			"&login=" . rawurlencode((string) $this->login) .
			"&contract=" . rawurlencode($md5_contract) .
			"&pin=" . rawurlencode($md5_pin) .
			"&cms_folder=" . rawurlencode((string) $this->cms_folder) .
			"&php_version=" . rawurlencode((string) $this->php_version) .
			"&mysql_version=" . rawurlencode((string) $this->mysql_version) .
			"&update_id=" . $this->update_id .
			"&current=" . intval($this->page) .
			"&limit=" . intval($this->limit) .
			"&backend=" . rawurlencode((string) $this->backend);

		if ($this->search != '')
		{
			$url .= "&search=" . rawurlencode($this->search);
		}

		if (is_numeric($this->category_id))
		{
			$url .= "&category_id=" . intval($this->category_id);
		}
		elseif (is_array($this->category_id))
		{
			foreach ($this->category_id as $iCategory)
			{
				$url .= "&category_id[]=" . intval($iCategory);
			}
		}

		!is_null($this->installMode) && $this->installMode && $url .= '&installMode';
		!is_null($this->order) && $url .= "&order=" . rawurlencode($this->order);

		$maxExecutionTime = intval(ini_get('max_execution_time'));

		$Core_Http = Core_Http::instance()
			->url($url)
			->timeout($maxExecutionTime > 0 ? $maxExecutionTime - 3 : 20)
			->referer(Core_Array::get($_SERVER, 'REQUEST_SCHEME', 'http') . '://' . Core_Array::get($_SERVER, 'HTTP_HOST'))
			->execute();

		$data = $Core_Http->getDecompressedBody();

		$oXml = @simplexml_load_string($data);

		if (is_object($oXml))
		{
			if (!intval($oXml->error))
			{
				$this->_parseGroup($oXml->shop_group);

				$aShop_Items = array();
				if (isset($oXml->shop_item) && count($oXml->shop_item))
				{
					foreach ($oXml->shop_item as $value)
					{
						$oObject = new StdClass();
						$oObject->id = intval($value->attributes()->id);

						$shop_group_id = intval($value->shop_group_id);

						$oObject->category_id = $shop_group_id;
						$oObject->category_name = isset($this->_aShop_Groups[$shop_group_id])
							? $this->_aShop_Groups[$shop_group_id]->name
							: '';

						$oObject->name = strval($value->name);
						$oObject->description = strval($value->description);
						$oObject->image_large = 'https://' . $this->update_server . strval($value->dir) . strval($value->image_large);
						$oObject->image_small = 'https://' . $this->update_server . strval($value->dir) . strval($value->image_small);
						$oObject->url = 'https://' . $this->update_server . strval($value->url) . '?contract=' . $md5_contract . '&pin=' . $md5_pin;
						$oObject->siteuser_id = intval($value->siteuser_id);
						$oObject->price = strval($value->price);
						$oObject->currency = strval($value->currency);
						// $oObject->isset_version = intval($value->isset_version);
						$oObject->isset_version = strval($value->isset_version);
						$oObject->paid = isset($value->paid)
							? intval($value->paid)
							: 0;

						$oAdminModule = Core_Entity::factory('Module')->getByPath(strval($value->path), FALSE);
						$oObject->installed = !is_null($oAdminModule) ? 1 : 0;

						$aShop_Items[] = $oObject;
					}

					$this->items = $aShop_Items;
				}
			}
		}
		else
		{
			throw new Core_Exception(
				Core::_('Market.server_error_respond_12'), array(), 0, FALSE
			);
		}

		$this->category_id = isset($oXml->category_id)
			? intval($oXml->category_id)
			: 0;

		$this->total = isset($oXml->total)
			? intval($oXml->total)
			: 0;

		$this->page = isset($oXml->page)
			? intval($oXml->page)
			: 1;

		$this->error = isset($oXml->error)
			? intval($oXml->error)
			: 0;
		//}

		return $this;
	}

	/**
	 * StdClass
	 * @var mixed
	 */
	protected $_Module = NULL;

	/**
	 * Загрузка приложения
	 *
	 * @param int $module_id update ID
	 * @return string
	 */
	public function getModule($module_id)
	{
		Core_Database::instance()->query('SET SESSION wait_timeout = 600');

		$url = 'https://' . $this->update_server . "/hostcmsupdate/market/?action=get_module&domain=" . rawurlencode((string) $this->domain) .
			'&protocol=' . rawurlencode((string) $this->protocol) .
			'&login=' . rawurlencode((string) $this->login) .
			'&contract=' . rawurlencode(md5($this->contract)) .
			'&pin=' . rawurlencode(md5($this->pin)) .
			'&cms_folder=' . rawurlencode($this->cms_folder) .
			'&php_version=' . rawurlencode($this->php_version) .
			'&mysql_version=' . rawurlencode($this->mysql_version) .
			'&update_id=' . $this->update_id .
			'&module_id=' . intval($module_id) .
			'&current=' . intval($this->page) .
			'&limit=' . intval($this->limit) .
			"&backend=" . rawurlencode((string) $this->backend);

		if (is_numeric($this->category_id))
		{
			$url .= "&category_id=" . intval($this->category_id);
		}
		elseif (is_array($this->category_id))
		{
			foreach ($this->category_id as $iCategory)
			{
				$url .= "&category_id[]=" . intval($iCategory);
			}
		}

		!is_null($this->installMode) && $this->installMode && $url .= '&installMode';

		//echo htmlspecialchars($url);

		$maxExecutionTime = intval(ini_get('max_execution_time'));

		$Core_Http = Core_Http::instance()
			->url($url)
			->timeout($maxExecutionTime > 0 ? $maxExecutionTime - 3 : 20)
			->referer(Core_Array::get($_SERVER, 'REQUEST_SCHEME', 'http') . '://' . Core_Array::get($_SERVER, 'HTTP_HOST'))
			->execute();

		$data = $Core_Http->getDecompressedBody();

		if (empty($data))
		{
			throw new Core_Exception(
				Core::_('Update.server_return_empty_answer')
			);
		}

		$oXml = @simplexml_load_string($data);

		if (is_object($oXml))
		{
			$error = intval($oXml->error);

			if (!$error)
			{
				if (isset($oXml->module) && count($oXml->module))
				{
					if (!defined('DENY_INI_SET') || !DENY_INI_SET)
					{
						if (Core::isFunctionEnable('set_time_limit') && ini_get('safe_mode') != 1 && ini_get('max_execution_time') < 120)
						{
							@set_time_limit(120);
						}
					}

					// Объект с данными о модуле
					$this->_Module = new StdClass();

					if (intval($oXml->module->attributes()->id))
					{
						$this->_Module->id = intval($oXml->module->attributes()->id);
						$this->_Module->shop_item_id = intval($oXml->module->shop_item_id);
						$this->_Module->name = html_entity_decode(strval($oXml->module->name), ENT_COMPAT, 'UTF-8');
						$this->_Module->description = strval($oXml->module->description);
						$this->_Module->number = strval($oXml->module->number);
						$this->_Module->path = strval($oXml->module->path);
						$this->_Module->php = strval($oXml->module->php);
						$this->_Module->sql = strval($oXml->module->sql);
						$this->_Module->file = strval($oXml->module->file);
						$this->_Module->author_email = strval($oXml->module->author_email);
					}

					if ($this->_Module->id)
					{
						// Загружаем и распаковываем версию модуля
						/**
						 * Структура архива
						 * files
						 * -- admin
						 * -- modules
						 * module.xml
						 * module.php
						 * module.sql
						 */

						// Временная директория для распаковки модуля
						// CMS_FOLDER . 'hostcmsfiles/tmp/install/{id}/'
						$this->tmpDir = $this->getPath() . DIRECTORY_SEPARATOR . $this->_Module->shop_item_id;

						// Удаляем директорию с данными предыдущей установки (5 mins)
						$bExists = Core_File::isDir($this->tmpDir)
							&& Core_File::isFile($this->tmpDir . DIRECTORY_SEPARATOR . 'module.xml')
							&& filemtime($this->tmpDir) + 60 * 5 > time();

						if (!$bExists)
						{
							if ($this->_Module->file != '')
							{
								Core_File::isDir($this->tmpDir) && Core_File::deleteDir($this->tmpDir);

								// Создаем директорию снова
								Core_File::mkdir($this->tmpDir, CHMOD, TRUE);

								$source_file = $this->tmpDir . DIRECTORY_SEPARATOR . 'tmpfile.tar.gz';

								$bTarGzExists = Core_File::isFile($source_file) && filemtime($source_file) + 60 * 5 > time();

								// Файла нет или не прошло 5 минут с момента создания
								if (!$bTarGzExists)
								{
									$Core_Http = $this->getModuleFile($this->_Module->file);

									// Сохраняем tar.gz
									Core_File::write($source_file, $Core_Http->getDecompressedBody());
								}

								if (Core_File::filesize($source_file))
								{
									// Распаковываем файлы
									$Core_Tar = new Core_Tar($source_file, 'gz');
									// $Core_Tar->addReplace('admin/', Core::$mainConfig['backend'] . '/');
									if (!$Core_Tar->extractModify($this->tmpDir, $this->tmpDir))
									{
										// Возникла ошибка распаковки
										throw new Core_Exception(
											Core::_('Update.update_files_error')
										);
									}
								}
								else
								{
									throw new Core_Exception(
										Core::_('Market.server_error_respond_15'), array(), 0, FALSE
									);
								}
							}
							else
							{
								throw new Core_Exception(
									Core::_('Market.server_error_respond_14'), array(), 0, FALSE
								);
							}
						}

						return $this->_Module;
					}
					else
					{
						$error = 13;
					}
				}
			}

			if ($error > 0)
			{
				$sModuleName = $error < 10 ? 'Update' : 'Market';

				throw new Core_Exception(
					Core::_($sModuleName . '.server_error_respond_' . $error)
				);
			}
		}

		return NULL;
	}

	/**
	 * Show module options
	 * @return self
	 */
	public function showModuleOptions()
	{
		// Читаем modules.xml
		$oModuleXml = $this->_ModuleXml;

		if (is_object($oModuleXml))
		{
			$aXmlFields = $oModuleXml->xpath("fields/field");

			if (count($aXmlFields))
			{
				$aFields = $this->getFields($aXmlFields);

				$oMainTab = Admin_Form_Entity::factory('Tab')->name('main');

				foreach ($aFields as $aFieldsValue)
				{
					$oForm_Field = $this->getFormField($aFieldsValue);

					$oMainTab->add($oForm_Field);
				}

				Admin_Form_Entity::factory('Form')
					->controller($this->controller)
					->action($this->controller->getPath())
					->add($oMainTab)
					->add(
						Admin_Form_Entity::factory('Input')
							->name('install')
							->value($this->_Module->shop_item_id)
							->class('hidden')
					)
					->add(Admin_Form_Entity::factory('Button')
						->name('applyOptions')
						->type('submit')
						->value(Core::_('market.install'))
						->class('applyButton btn btn-blue')
						->onclick(
							$this->controller->getAdminSendForm(array('action' => 'sendOptions'))
						)
					)
					->execute();
			}
		}

		return $this;
	}

	/**
	 * Get form field
	 * @param array $aFieldsValue
	 * @return Form_Field_Model
	 */
	public function getFormField($aFieldsValue)
	{
		$sFieldCaption = htmlspecialchars($aFieldsValue['Caption']);
		$sFieldName = $aFieldsValue['Name'];
		$sFieldValue = $aFieldsValue['Value'];
		$sFieldType = strval($aFieldsValue['Type']);

		switch ($sFieldType)
		{
			case 'input':
			default:
				$oForm_Field = Admin_Form_Entity::factory('Div')
					->class('row')
					->add(
						Admin_Form_Entity::factory('Input')
							->caption($sFieldCaption)
							->name($sFieldName)
							->value($sFieldValue)
							->divAttr(array('class' => 'form-group col-xs-6'))
							->controller($this->controller)
					);
			break;

			case 'checkbox':
				$oForm_Field = Admin_Form_Entity::factory('Div')
					->class('row')
					->add(
						Admin_Form_Entity::factory('Checkbox')
							->caption($sFieldCaption)
							->name($sFieldName)
							->value($sFieldValue !== '' ? $sFieldValue : 1)
							->divAttr(array('class' => 'form-group col-xs-6'))
							->controller($this->controller)
					);
			break;

			case 'radiogroup':
				$oForm_Field = Admin_Form_Entity::factory('Div')
					->class('row')
					->add(
						Admin_Form_Entity::factory('Radiogroup')
							->caption($sFieldCaption)
							->name($sFieldName)
							->value($sFieldValue)
							->radio($aFieldsValue['ListValues'])
							->divAttr(array('class' => 'form-group col-xs-6'))
							->controller($this->controller)
					);
			break;

			case 'select':
				$oForm_Field = Admin_Form_Entity::factory('Div')->class('row')
					->add(
						$oAdmin_Form_Entity_Select = Admin_Form_Entity::factory('Select')
							->caption($sFieldCaption)
							->name($sFieldName)
							->value($sFieldValue)
							->options($aFieldsValue['ListValues'])
							->divAttr(array('class' => 'form-group col-xs-6'))
							->controller($this->controller)
					);

					$aFieldsValue['Multiple']
						&& $oAdmin_Form_Entity_Select
							->multiple('multiple')
							->size(!is_null($aFieldsValue['Size']) ? $aFieldsValue['Size'] : 5);
			break;

			case 'siteList':
			case 'shopList':
			case 'informationsystemList':

				if ($sFieldType == 'siteList')
				{
					$oUser = Core_Auth::getCurrentUser();
					$aObjects = $oUser->getSites();
				}
				elseif ($sFieldType == 'shopList')
				{
					$aObjects = Core_Entity::factory('Site', CURRENT_SITE)
						->Shops
						->findAll();
				}
				elseif ($sFieldType == 'informationsystemList')
				{
					$aObjects = Core_Entity::factory('Site', CURRENT_SITE)
						->Informationsystems
						->findAll();
				}

				$aTmpOptions = array();
				foreach ($aObjects as $oObject)
				{
					$aTmpOptions[$oObject->id] = $oObject->name;
				}

				$oForm_Field = Admin_Form_Entity::factory('Div')->class('row')
					->add(
						Admin_Form_Entity::factory('Select')
							->caption($sFieldCaption)
							->name($sFieldName)
							->options($aTmpOptions)
							->divAttr(array('class' => 'form-group col-xs-6'))
							->controller($this->controller)
					);
			break;

			case 'file':
				$sFieldExtension = htmlspecialchars($aFieldsValue['Extension']);
				$sFieldMaxWidth = intval($aFieldsValue['MaxWidth']);
				$sFieldMaxHeight = intval($aFieldsValue['MaxHeight']);

				$oForm_Field = Admin_Form_Entity::factory('Div')->class('row')
					->add(
						Admin_Form_Entity::factory('File')
							->caption($sFieldCaption)
							->name($sFieldName)
							->value($sFieldValue)
							->largeImage(
								array('show_params' => FALSE)
							)->smallImage(
								array('show' => FALSE)
							)
							->divAttr(array('class' => 'form-group col-xs-6'))
							->controller($this->controller)
					)
					->add(
						Admin_Form_Entity::factory('Div')
							->class('col-xs-6 margin-top-21')
							->value(
								(trim($sFieldExtension) != ''
									? Core::_('market.allowed_extension', $sFieldExtension)
									: ''
								)
						)
					)
					->add(
						Admin_Form_Entity::factory('Div')
							->class('col-xs-6')
							->value(
								$sFieldMaxWidth > 0 && $sFieldMaxHeight > 0
									? "\n" . Core::_('market.max_file_size', $sFieldMaxWidth, $sFieldMaxHeight)
									: ''
								)
					);
			break;

			case 'textarea':
				$oForm_Field = Admin_Form_Entity::factory('Div')->class('row')
					->add(
						Admin_Form_Entity::factory('Textarea')
							->caption($sFieldCaption)
							->name($sFieldName)
							->value($sFieldValue)
							->divAttr(array('class' => 'form-group col-xs-6'))
							->controller($this->controller)
					);
			break;
		}

		return $oForm_Field;
	}

	/**
	 * Module xml
	 * @var mixed
	 */
	protected $_ModuleXml = NULL;

	/**
	 * Parse module xml
	 * @return string
	 */
	public function parseModuleXml()
	{
		$sModuleXmlPath = $this->tmpDir . DIRECTORY_SEPARATOR . 'module.xml';

		if (Core_File::isFile($sModuleXmlPath))
		{
			$sModuleXml = Core_File::read($sModuleXmlPath);
			$this->_ModuleXml = simplexml_load_string($sModuleXml);
		}

		return $this->_ModuleXml;
	}

	/**
	 * Array of files to upload after install
	 */
	protected $_uploadFiles = array();

	public function applyModuleOptions()
	{
		// Читаем modules.xml
		$oModuleXml = $this->_ModuleXml;

		if (is_object($oModuleXml))
		{
			$aXmlFields = $oModuleXml->xpath("fields/field");

			if (count($aXmlFields))
			{
				$aFields = $this->getFields($aXmlFields);

				$aOptions = array();
				foreach ($aFields as $aFieldsValue)
				{
					$sFieldCaption = $aFieldsValue['Caption'];
					$sFieldName = $aFieldsValue['Name'];
					$sFieldType = $aFieldsValue['Type'];

					// Файл
					if ($sFieldType == 'file')
					{
						if (isset($_FILES[$sFieldName]['tmp_name'])
						&& Core_File::isFile($_FILES[$sFieldName]['tmp_name'])
						&& $_FILES[$sFieldName]['size'] > 0)
						{
							$sFieldPath = ltrim($aFieldsValue['Path'], '/');
							$sFieldExtension = $aFieldsValue['Extension'];

							$sExt = Core_File::getExtension($_FILES[$sFieldName]['name']);
							$aAllowedExt = explode(',', $sFieldExtension);

							if (strlen(trim($sFieldExtension)) == 0 || in_array($sExt, $aAllowedExt))
							{
								$this->_uploadFiles[] = array(
									'source' => $_FILES[$sFieldName]['tmp_name'],
									'destination' => CMS_FOLDER . $sFieldPath
								);

								//Core_File::moveUploadedFile($_FILES[$sFieldName]['tmp_name'], CMS_FOLDER . $sFieldPath);
							}
							else
							{
								throw new Core_Exception(
									Core::_('install.file_disabled_extension', $sFieldCaption)
								);
							}
						}
					}
					// Остальные типы полей
					else
					{
						$aOptions[$sFieldName] = Core_Array::getPost($sFieldName);
					}
				}

				$this->options = $aOptions;
			}
		}

		return $this;
	}

	/**
	 * Install
	 */
	public function install()
	{
		// Копируем файлы из ./files/ в папку системы
		$sFilesDir = $this->tmpDir . DIRECTORY_SEPARATOR . 'files';
		if (Core_File::isDir($sFilesDir))
		{
			if (Core::$mainConfig['backend'] !== 'admin' && Core_File::isDir($sFilesDir . '/admin'))
			{
				Core_File::rename($sFilesDir . '/admin', $sFilesDir . '/' . Core::$mainConfig['backend']);
			}

			Core_Log::instance()->clear()
				->status(Core_Log::$MESSAGE)
				->write('Market, copy `files` directory');

			Core_File::copyDir($sFilesDir, CMS_FOLDER);
		}

		foreach ($this->_uploadFiles as $aUploadFile)
		{
			try
			{
				Core_File::moveUploadedFile($aUploadFile['source'], $aUploadFile['destination']);
			}
			catch (Exception $e)
			{
				Core_Message::show($e->getMessage(), 'error');
			}
		}

		// Размещаем SQL из описания обновления
		$sSql = strval($this->_Module->sql);
		$sSqlFilename = $this->tmpDir . '/' . $this->_Module->id . '.sql';
		Core_File::write($sSqlFilename, html_entity_decode($sSql, ENT_COMPAT, 'UTF-8'));
		$sSqlCode = html_entity_decode($sSql, ENT_COMPAT, 'UTF-8');
		Sql_Controller::instance()->execute($sSqlCode);

		// Размещаем PHP из описания обновления
		$sPhp = strval($this->_Module->php);
		$sPhpFilename = $this->tmpDir . '/' . $this->_Module->id . '.php';
		Core_File::write($sPhpFilename, html_entity_decode($sPhp, ENT_COMPAT, 'UTF-8'));
		include($sPhpFilename);

		// Стандартный файл module.sql из поставки модуля
		$sSqlModuleFilename = $this->tmpDir . DIRECTORY_SEPARATOR . 'module.sql';
		if (Core_File::isFile($sSqlModuleFilename))
		{
			Core_Log::instance()->clear()
				->status(Core_Log::$MESSAGE)
				->write('Market, execute module.sql');

			$sSqlCode = Core_File::read($sSqlModuleFilename);
			Sql_Controller::instance()->execute($sSqlCode);
		}

		// Стандартный файл module.php из поставки модуля
		$sPhpModuleFilename = $this->tmpDir . DIRECTORY_SEPARATOR . 'module.php';
		if (Core_File::isFile($sPhpModuleFilename))
		{
			Core_Log::instance()->clear()
				->status(Core_Log::$MESSAGE)
				->write('Market, execute module.php');

			include($sPhpModuleFilename);
		}

		if (is_object($this->_ModuleXml))
		{
			$create_module = strval($this->_ModuleXml->options->create_module);
			$bCreateModule = $create_module == 1 || $create_module == 'true';

			// Создаем модуль только при явном указании на это
			if ($bCreateModule)
			{
				Core_Log::instance()->clear()
					->status(Core_Log::$MESSAGE)
					->write('Market, create module');

				$oAdminModule = Core_Entity::factory('Module');
				$oAdminModule
					->name($this->_Module->name)
					->description($this->_Module->description)
					->active(1)
					->indexing(1)
					->path($this->_Module->path)
					->save();

				// install() для модуля, если есть
				$oAdminModule->setupModule();

				echo '<script>$.loadNavSidebarMenu({moduleName: \'' . Core_Str::escapeJavascriptVariable($oAdminModule->path) . '\'})</script>';
			}
			else
			{
				echo '<script>$.loadSiteList()</script>';
			}
		}

		Core_Log::instance()->clear()
			->status(Core_Log::$MESSAGE)
			->write('Market, module installation is complete');

		clearstatcache();

		// Удаляем папку с файлами в случае с успешной установкой
		Core_File::isDir($this->tmpDir) && Core_File::deleteDir($this->tmpDir);
	}

	/**
	 * Get fields
	 * @param array $array
	 * @return array
	 */
	public function getFields(array $array)
	{
		$return = array();

		// цикл по дереву 'fields'
		foreach ($array as $aFieldsValue)
		{
			$tmp = array(
				'Caption' => strval($aFieldsValue->caption),
				'Type' => strval($aFieldsValue->attributes()->type),
				'Value' => strval($aFieldsValue->value),
				'Name' => strval($aFieldsValue->name),
				'Path' => strval($aFieldsValue->path),
				'Extension' => strval($aFieldsValue->extension),
				'MaxWidth' => strval($aFieldsValue->max_width),
				'MaxHeight' => strval($aFieldsValue->max_height),
				'Multiple' => FALSE,
				'Size' => NULL,
				'ListValues' => array()
			);

			if (in_array($tmp['Type'], array('select', 'radiogroup')))
			{
				// Значения для списка
				$aXmlValues = $aFieldsValue->xpath("value/list");
				if (count($aXmlValues))
				{
					foreach ($aXmlValues as $oXmlValue)
					{
						$tmp['ListValues'][strval($oXmlValue->attributes()->value)] = strval($oXmlValue);
					}
				}

				// multiple
				$multiple = strval($aFieldsValue->multiple);
				if ($multiple === '1' || $multiple === 'true')
				{
					$tmp['Multiple'] = TRUE;
				}

				// multiple size
				$size = strval($aFieldsValue->size);
				if ($size > 0)
				{
					$tmp['Size'] = intval($size);
				}
			}

			$return[] = $tmp;
		}

		return $return;
	}

	/**
	 * Загрузка файла модуля
	 *
	 * @param string $path
	 * @return Core_Http
	 */
	public function getModuleFile($path)
	{
		$url = 'https://' . $this->update_server . $path . "&domain=".rawurlencode((string) $this->domain) .
			'&protocol=' . rawurlencode((string) $this->protocol) .
			"&login=" . rawurlencode((string) $this->login) .
			"&contract=" . rawurlencode(md5($this->contract)) .
			"&pin=" . rawurlencode(md5($this->pin)) .
			"&cms_folder=" . rawurlencode($this->cms_folder) .
			"&php_version=" . rawurlencode($this->php_version) .
			"&mysql_version=" . rawurlencode($this->mysql_version) .
			"&update_id=" . $this->update_id .
			"&backend=" . rawurlencode((string) $this->backend);

		!is_null($this->installMode) && $this->installMode && $url .= '&installMode';

		$maxExecutionTime = intval(ini_get('max_execution_time'));

		$Core_Http = Core_Http::instance()
			->url($url)
			->timeout($maxExecutionTime > 0 ? $maxExecutionTime - 3 : 20)
			->referer(Core_Array::get($_SERVER, 'REQUEST_SCHEME', 'http') . '://' . Core_Array::get($_SERVER, 'HTTP_HOST'))
			->execute();

		return $Core_Http;
	}

	/**
	 * Options
	 * @var array
	 */
	protected $_aTmpOptions = array();

	/**
	 * Get category options
	 * @param integer $parentId
	 * @param integer $level
	 */
	protected function _getCategoryOptions($parentId, $level = 0)
	{
		if (isset($this->_categories[$parentId]))
		{
			foreach ($this->_categories[$parentId] as $object)
			{
				$this->_aTmpOptions[$object->id] = str_repeat('—', $level) . " " . $object->name;

				if ($object->count)
				{
					$this->_aTmpOptions[$object->id] .= " (" . $object->count . ")";
				}

				$this->_getCategoryOptions($object->id, $level + 1);
			}
		}
	}

	/**
	 * Show items list
	 */
	public function showItemsList()
	{
		$sContent = '';

		if ($this->error == 0)
		{
			$this->_aTmpOptions = array(Core::_('Market.select_section'));
			$this->_getCategoryOptions(0);

			ob_start();
			?><div class="market-wrapper">
				<header class="market-header">
					<div class="custom-select">
						<?php
						Admin_Form_Entity::factory('Select')
							->name('category_id')
							->value($this->category_id)
							->onchange('changeCategory(this)')
							->options($this->_aTmpOptions)
							->divAttr(array('class' => ''))
							->class('custom-select')
							->execute();
						?>
					</div>
					<div class="search-box">
						<?php
						Admin_Form_Entity::factory('Input')
							->name('search_query')
							->class('')
							->placeholder(Core::_('Market.search_placeholder'))
							->divAttr(array('class' => 'w-100'))
							->add(
								Admin_Form_Entity::factory('Code')->html('<button class="search-btn" type="submit" onclick="$.adminSendForm({buttonObject: $(this), action: \'sendSearchQuery\', windowId: \'id_content\'}); return false"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg></button>')
						)
						->value(Core_Array::getRequest('search_query'))
						->execute();
						?>
					</div>
				</header>
			<?php

			$sHtml = $this->getMarketItemsHtml();

			$count_pages = ceil($this->total / $this->limit);

			Admin_Form_Entity::factory('Code')->html($sHtml)
				->execute();

			if ($this->category_id && $count_pages > 1)
			{
				ob_start();

				$this->controller
					->limit($this->limit)
					->setTotalCount($this->total)
					->pageNavigation();

				$sFooter = '<div class="col-xs-12">' . ob_get_clean() . '</div>';

				Admin_Form_Entity::factory('Div')->class('row')
					->add(
						Admin_Form_Entity::factory('Code')->html($sFooter)
					)
					->execute();
			}

			?></div><?php

			$sContent = ob_get_clean();
		}
		else
		{
			$aReturn = Update_Controller::instance()->parseUpdates();

			$sDatetime = !is_null($aReturn['datetime'])
				? Core_Date::strftime(DATE_TIME_FORMAT, strtotime($aReturn['datetime']))
				: '';

			throw new Core_Exception(
				Core::_('Update.server_error_respond_' . $this->error, $sDatetime), array(), 0, FALSE
			);
		}

		$sWindowId = $this->controller->getWindowId();

		Admin_Form_Entity::factory('Form')
			->controller($this->controller)
			->action($this->controller->getPath())
			->add(Admin_Form_Entity::factory('Code')->html($sContent))
			->add(Admin_Form_Entity::factory('Code')
				->html('<script>
				function changeCategory(object)
				{
					if (object && object.tagName == "SELECT")
					{
						category_id = parseInt(object.options[object.selectedIndex].value);
						$.adminLoad({path: "' . $this->path . '", windowId:"' . $sWindowId . '", additionalParams: "category_id=" + category_id, current: 1});
					}
					return false;
				}</script>')
			)
			->execute();
	}

	/**
	 * Get market items html
	 * @return string
	 */
	public function getMarketItemsHtml()
	{
		$sHtml = '<main class="app-grid">';
		foreach ($this->items as $object)
		{
			$sHtml .= $this->getMarketItemHtml($object);
		}
		$sHtml .= '</main>';

		return $sHtml;
	}

	/**
	 * Get market item html
	 * @param object $object
	 * @return string
	 */
	public function getMarketItemHtml($object)
	{
		$sWindowId = $this->controller
			? $this->controller->getWindowId()
			: 'id_content';

		ob_start();

		?><article class="app-card">
			<div class="app-card-header">
				<div class="app-icon">
					<a target="_blank" href="<?php echo htmlspecialchars($object->url)?>">
						<img src="<?php echo htmlspecialchars($object->image_small)?>" style="width:80px; height:80px;" class="market-item-image bordered-3 bordered-white" />
					</a>
				</div>
				<div class="app-title-group">
					<h2 class="app-title"><a target="_blank" href="<?php echo htmlspecialchars($object->url)?>"><?php echo htmlspecialchars($object->name)?></a></h2>
					<span class="app-category"><?php echo htmlspecialchars($object->category_name)?></span>
				</div>
			</div>
			<div class="app-card-body">
				<p class="app-description"><?php echo $object->description?></p>
			</div>
			<div class="app-card-footer">
				<?php
				if ($object->installed)
				{
					?> <button class="btn btn-installed" disabled aria-disabled="true">
         				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
          				<?php echo Core::_('Market.installed')?>
        			</button><?php
				}
				else
				{
					?><span class="app-price"><?php
						echo floatval($object->price)
							? number_format(round($object->price), 0, ',', ' ') . ' ' . (
								$object->currency == 'руб.'
									? '<i class="fa-solid fa-ruble-sign"></i>'
									: $object->currency
							)
							: Core::_('Market.free');
					?></span>
					<?php
					if ($object->isset_version)
					{
						if ($object->paid && !$object->installed || $object->price == 0)
						{
							$onclick = "res=confirm('" . Core::_('Market.install_warning') . "'); if (res){ $.adminLoad({path: '{$this->path}', action:'{$this->itemAction}', operation:'', additionalParams:'install=" . $object->id . "&category_id=" . $this->category_id . "&current=" . $this->page . "', windowId:'" . $sWindowId . "'}); } return false";

							$href = $this->path . "?hostcms[window]={$sWindowId}&=hostcms[action]={$this->itemAction}&install={$object->id}&category_id={$this->category_id}&current={$this->page}";

							?><a class="btn btn-install" onclick="<?php echo $onclick?>" href="<?php echo $href?>">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
								<?php echo Core::_('Market.install')?>
							</a><?php
						}
						else
						{
							?><a class="btn btn-buy" target="_blank" href="<?php echo htmlspecialchars($object->url)?>">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
								<?php echo Core::_('Market.buy')?>
							</a><?php
						}
					}
					else
					{
						?><button class="btn btn-unavailable" disabled aria-disabled="true">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
							<?php echo Core::_('Market.version_absent')?>
						</button><?php
					}
				}
			?></div>
		</article><?php

		return ob_get_clean();
	}
}