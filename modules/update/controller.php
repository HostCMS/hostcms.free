<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Updates.
 *
 * @package HostCMS
 * @subpackage Update
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Update_Controller extends Core_Servant_Properties
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
		'install_beta',
		'keys',
		'modules',
		'protocol'
	);

	/**
	 * The singleton instances.
	 * @var mixed
	 */
	static public $instance = NULL;

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
	 * Get directory path
	 * @return string
	 */
	public function getPath()
	{
		return CMS_FOLDER . 'hostcmsfiles/update';
	}

	/**
	 * Get update file path
	 * @return string
	 */
	public function getFilePath()
	{
		return $this->getPath() . '/updatelist.xml';
	}

	public function deleteUpdateFile()
	{
		$filePath = $this->getFilePath();

		if (Core_File::isFile($filePath))
		{
			Core_File::delete($filePath);
		}

		$this->_xml = NULL;

		return $this;
	}

	/**
	 * Xml
	 * @var mixed
	 */
	protected $_xml = NULL;

	/**
	 * Get xml
	 * @return string
	 */
	protected function _getXml()
	{
		if (is_null($this->_xml))
		{
			$this->_xml = '';

			$updateFilePath = $this->getFilePath();

			if (!file_exists($updateFilePath) || time() >= filemtime($updateFilePath) + 4 * 60 * 60)
			{
				if (!defined('HOSTCMS_USER_LOGIN')
					|| !defined('HOSTCMS_CONTRACT_NUMBER')
					|| !defined('HOSTCMS_PIN_CODE')
					|| !defined('HOSTCMS_UPDATE_NUMBER')
				)
				{
					throw new Core_Exception(Core::_('Update.constant_check_error'), array(), 0, FALSE);
				}

				if (!defined('HOSTCMS_UPDATE_SERVER'))
				{
					throw new Core_Exception(Core::_('Update.update_constant_error'), array(), 0, FALSE);
				}

				$this
					->setUpdateOptions()
					->getUpdates();
			}

			if (file_exists($updateFilePath))
			{
				$xml = Core_File::read($updateFilePath);

				!empty($xml)
				    && $this->_xml = @simplexml_load_string($xml);
			}
			else
			{
				throw new Core_Exception(Core::_('Update.error_open_updatefile'), array(), 0, FALSE);
			}
		}

		return $this->_xml;
	}

	/**
	 * Set update options
	 * @return self
	 */
	public function setUpdateOptions()
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

		// Modules
		$aTmpModules = array();

		$aModules = Core_Entity::factory('Module')->getAllByActive(1);
		foreach ($aModules as $oModule)
		{
			$oModule->loadModule();

			$oCore_Module = $oModule->Core_Module;

			if ($oCore_Module)
			{
				$aTmpModules[] = array(
					'path' => $oModule->path,
					'version' => $oCore_Module->version,
					'date' => $oCore_Module->date
				);
			}
		}

		$this
			->login(defined('HOSTCMS_USER_LOGIN') ? HOSTCMS_USER_LOGIN : '')
			->contract(defined('HOSTCMS_CONTRACT_NUMBER') ? HOSTCMS_CONTRACT_NUMBER : '')
			->pin(defined('HOSTCMS_PIN_CODE') ? HOSTCMS_PIN_CODE : '')
			->cms_folder(CMS_FOLDER)
			->php_version(phpversion())
			->mysql_version(Core_DataBase::instance()->getVersion())
			->update_id($update_id)
			->domain($domain)
			->update_server(HOSTCMS_UPDATE_SERVER)
			->install_beta(defined('INSTALL_BETA_UPDATE') && INSTALL_BETA_UPDATE ? 1 : 0)
			->keys($aSite_Alias_Names)
			->modules($aTmpModules)
			->protocol($oSite->https ? 'https' : 'http');

		return $this;
	}

	/**
	 * Загрузка XML с доступными обновлениями
	 *
	 * @return Update_Controller
	 */
	public function getUpdates()
	{
		// Формируем строку запроса
		$url = 'https://' . $this->update_server . '/hostcmsupdate/?action=get_listupdate&domain=' . rawurlencode($this->domain) .
			'&protocol=' . rawurlencode($this->protocol) .
			'&login=' . rawurlencode($this->login) .
			'&contract=' . rawurlencode(md5($this->contract)) .
			'&pin=' . rawurlencode(md5($this->pin)) .
			'&cms_folder=' . rawurlencode($this->cms_folder) .
			'&php_version=' . rawurlencode($this->php_version) .
			'&mysql_version=' . rawurlencode($this->mysql_version) .
			'&update_id=' . $this->update_id .
			'&install_beta_update=' . rawurlencode($this->install_beta);

		$Core_Http = Core_Http::instance('curl')
			->timeout(15)
			->method('POST');

		if (is_array($this->keys))
		{
			foreach ($this->keys as $key => $keyName)
			{
				$Core_Http->data('key[' . $key . ']', $keyName);
			}
		}

		if (is_array($this->modules))
		{
			foreach ($this->modules as $key => $aModule)
			{
				$Core_Http->data('module[' . $key . ']', $aModule['path']);
				$Core_Http->data('moduleVersion[' . $key . ']', $aModule['version']);
			}
		}

		$Core_Http
			->url($url)
			->referer(Core_Array::get($_SERVER, 'HTTP_HOST'))
			->execute();

		$sBody = $Core_Http->getDecompressedBody();

		if (!empty($sBody))
		{
			$aHeaders = $Core_Http->parseHeaders();

			if (isset($aHeaders['status']) && $Core_Http->parseHttpStatusCode($aHeaders['status']) == 200)
			{
				Core_File::write($this->getFilePath(), $sBody);
			}
			else
			{
				throw new Core_Exception('Wrong server answer, code "%code"!', array('%code' => Core_Array::get($aHeaders, 'status', 'undefined')), 0, FALSE);
			}
		}
		else
		{
			throw new Core_Exception('Request error code %code: %message', array('%code' => $Core_Http->getErrno(), '%message' => $Core_Http->getError()), 0, FALSE);
		}

		return $this;
	}

	/**
	 * Загрузка файла для обновления $update_key_id
	 *
	 * @param int $update_id update ID
	 * @return string
	 */
	public function getUpdate($update_key_id)
	{
		$url = 'https://' . $this->update_server . "/hostcmsupdate/?action=get_update&domain=".rawurlencode($this->domain) .
			'&protocol=' . rawurlencode($this->protocol) .
			"&login=" . rawurlencode($this->login) .
			"&contract=" . rawurlencode(md5($this->contract)) .
			"&pin=" . rawurlencode(md5($this->pin)) .
			"&cms_folder=" . rawurlencode($this->cms_folder) .
			"&php_version=" . rawurlencode($this->php_version) .
			"&mysql_version=" . rawurlencode($this->mysql_version) .
			"&update_id=" . $this->update_id . "&update_key_id=" . rawurlencode($update_key_id) . "&install_beta_update=" . rawurlencode($this->install_beta);

		$Core_Http = Core_Http::instance()
			->url($url)
			->timeout(15)
			->referer(Core_Array::get($_SERVER, 'HTTP_HOST'))
			->execute();

		return $Core_Http->getDecompressedBody();
	}

	/**
	 * Parse XML update file
	 * @return array
	 */
	public function parseUpdates()
	{
		// Дата окончания поддержки
		$return = array(
			'error' => 0,
			'expiration_of_support' => FALSE,
			'datetime' => NULL,
			'entities' => array()
		);

		$oXml = $this->_getXml();

		if (is_object($oXml))
		{
			foreach ($oXml->update as $value)
			{
				$id = (int)$value->attributes()->id;

				$return['entities'][$id] = $oUpdate_Entity = new Update_Entity();
				$oUpdate_Entity->setTableColums(array(
					'id' => array(),
					'name' => array(),
					'beta' => array(),
					'number' => array(),
					'description' => array()
				));

				$oUpdate_Entity->id = $id;
				$oUpdate_Entity->name = (string)$value->update_name;
				$oUpdate_Entity->beta = (int)$value->beta;
				$oUpdate_Entity->number = (string)$value->update_name;
				//$oUpdate_Entity->description = html_entity_decode((string)$value->update_description, ENT_COMPAT, 'UTF-8');
				$oUpdate_Entity->description = (string)$value->update_description;
			}

			$return['error'] = (int)$oXml->error;
			$return['expiration_of_support'] = (string)$oXml->expiration_of_support;
			$return['datetime'] = (string)$oXml->datetime;
		}

		return $return;
	}

	/**
	 * Parse XML module's file
	 * @return array
	 */
	public function parseModules()
	{
		$return = array();

		$oXml = $this->_getXml();

		if (is_object($oXml))
		{
			foreach ($oXml->module as $value)
			{
				$id = (int)$value->attributes()->id;

				$return[$id] = $oUpdate_Module_Entity = new Update_Module_Entity();
				$oUpdate_Module_Entity->setTableColums(array(
					'id' => array(),
					'name' => array(),
					'beta' => array(),
					'number' => array(),
					'path' => array(),
					'description' => array(),
					'file' => array(),
				));

				$oUpdate_Module_Entity->id = $id;
				$oUpdate_Module_Entity->name = (string)$value->name;
				$oUpdate_Module_Entity->beta = (int)$value->beta;
				$oUpdate_Module_Entity->number = (string)$value->number;
				$oUpdate_Module_Entity->path = (string)$value->path;
				$oUpdate_Module_Entity->description = '<div><strong>' . Core::_('Update.module', htmlspecialchars((string)$value->name)) . '</strong></div>' . (string)$value->description;
				$oUpdate_Module_Entity->file = (string)$value->file;
			}
		}

		return $return;
	}

	/**
	 * Загрузка XML со списком обновлений
	 *
	 * @param string $path
	 * @return Core_Http
	 */
	public function getUpdateFile($path)
	{
		$url = 'https://' . $this->update_server . $path . "&domain=".rawurlencode($this->domain) .
			'&protocol=' . rawurlencode($this->protocol) .
			"&login=" . rawurlencode($this->login) .
			"&contract=" . rawurlencode(md5($this->contract)) .
			"&pin=" . rawurlencode(md5($this->pin)) .
			"&cms_folder=" . rawurlencode($this->cms_folder) .
			"&php_version=" . rawurlencode($this->php_version) .
			"&mysql_version=" . rawurlencode($this->mysql_version) .
			"&update_id=" . $this->update_id . "&install_beta_update=" . rawurlencode($this->install_beta);

		$Core_Http = Core_Http::instance()
			->url($url)
			->timeout(20)
			->referer(Core_Array::get($_SERVER, 'HTTP_HOST'))
			->execute();

		return $Core_Http;
	}
}